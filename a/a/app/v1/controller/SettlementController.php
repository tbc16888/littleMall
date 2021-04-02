<?php
declare (strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;
use core\exception\BusinessException;
use core\service\cart\CartService;
use core\service\coupon\UserCouponService;
use core\service\goods\GoodsIntegralService;
use core\service\settlement\SettlementService;
use core\service\settlement\SettlementFrontModuleService;
use core\service\user\UserAddressService;
use core\service\user\UserIntegralService;

class SettlementController extends BaseController
{
    // 中间件
    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    public function initialize()
    {
        $userId = strval(session('user_id'));
        CartService::getInstance()->setUserId($userId);
        UserCouponService::getInstance()->setUserId($userId);
        UserAddressService::getInstance()->setUserId($userId);
        UserIntegralService::getInstance()->setUserId($userId);
    }

    /**
     * 结算信息
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        // 获取结算商品
        $cartId = $request->get('cart_id', '');
        $condition = [];
        $goodsList = CartService::getInstance()->setTableUniqueId($cartId)
            ->getCheckedList($condition);
        if (count($goodsList) <= 0) return finish(1, '没有可结算的商品');

        // 各个商品总价
        $settlementGoodsAmountList = SettlementService::getInstance()
            ->setSettlementGoods($goodsList)
            ->calcGoodsTotalPriceOfEach()->getGoodsTotalPriceOfEach();
        $goodsAmountTotal = array_sum($settlementGoodsAmountList);

        // 生成模块
        $moduleList = [];
        $moduleService = new SettlementFrontModuleService();
        $moduleList[] = $moduleService->goods($goodsList);
        $addressId = $request->get('address_id', '');
        $moduleList[] = $moduleService->address($addressId);
        $userCouponId = $request->get('user_coupon_id', '');
        $coupon = $moduleService->coupon($settlementGoodsAmountList, $userCouponId);
        $moduleList[] = $coupon;
        // 积分
        $integral = $moduleService->integral($goodsList,
            intval($request->get('integral', 0)));
        $moduleList[] = $integral;

        $settlement = [];
        foreach ($moduleList as $module) $settlement[$module['type']] = $module['data'];


        // 费用明细
        $orderAmount = $goodsAmountTotal;
        $orderAmount -= $coupon['data']['field']['discount_amount'];
        $orderAmount -= $integral['data']['field']['discount_amount'];

        $charges = [];
        $chargesList = [
            ['label' => '商品总额', 'text' => '', 'field' => 'goods_amount'],
            ['label' => '运费', 'text' => '+¥', 'field' => 'shipping_fee'],
            ['label' => '优惠券', 'text' => '-¥', 'field' => 'coupon_discount'],
            ['label' => '积分抵扣', 'text' => '-¥', 'field' => 'integral_discount'],
        ];

        $chargesData = [];
        $chargesData['goods_amount'] = $goodsAmountTotal;
        $chargesData['integral_discount'] =
            $integral['data']['field']['discount_amount'];
        $chargesData['coupon_discount'] =
            $coupon['data']['field']['discount_amount'];
        foreach ($chargesList as $item) {
            if (!isset($chargesData[$item['field']])) continue;
//            if (!$chargesData[$item['field']] && $item['field'] != 'shipping_fee') {
//                continue;
//            }
            $charges[] = ['label' => $item['label'],
                'text' => $item['text'] .
                    number_format(floatval($chargesData[$item['field']]), 2, '.', '')];
        }
        $settlement['charges'] = $charges;


        // 配送时间
        $settlement['delivery_time'] = $moduleService->deliveryTime($request->get('delivery_time', ''));

        // 配送方式
        $settlement['delivery_method'] = [];
        $settlement['delivery_method'][''] = [];

        // 自提点
        $settlement['outlets'] = [];

        // 提交
        $submit = $moduleService->submit();
        $submit['data']['field']['order_amount'] =
            number_format($orderAmount, 2, '.', '');
        $submit['data']['field']['integral'] = $integral['data']['field']['integral'];
        $settlement['submit'] = $submit['data'];
        return finish(0, '获取成功', $settlement);
    }


    /**
     * 提交结算
     * @RequestMapping(path="/submit", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function submit(Request $request)
    {
        if (!$this->request->isPost()) return finish(1, '非法访问');

        // 结算商品
        $cartId = $request->post('cart_id', '');
        $condition = [];
        $goodsList = CartService::getInstance()->setTableUniqueId($cartId)
            ->getCheckedList($condition);
        if (count($goodsList) <= 0) return finish(1, '没有可结算的商品');

        // 处理表单
        $formData = $request->getPostParams(['shipping_type', 'address_id', 'buyer_remark', 'user_coupon_id', 'integral']);
        $formData = SettlementService::getInstance()->setSettlementGoods($goodsList)
            ->setFormData($formData)->setUserId(session('user_id'))
            ->handleFormData()->getFormData();

        // 验证积分
        $integral = $formData['integral'];
        if ($integral && UserIntegralService::getInstance()->current(session('user_id')) < $integral) {
            return finish(1, '积分不足');
        }

        $formData = SettlementService::transaction(function () {
            $formData = SettlementService::getInstance()->submit()->getFormData();
            if ($formData['integral']) {
                UserIntegralService::getInstance()->change(
                    session('user_id'),
                    $formData['integral'] * -1,
                    UserIntegralService::CONSUME,
                    '订单' . $formData['order_sn']
                );
            }
            if ($formData['user_coupon_id']) {
                UserCouponService::getInstance()
                    ->changeStatus($formData['user_coupon_id'],
                        UserCouponService::USED, $formData['order_sn']);
            }
            CartService::getInstance()->db()
                ->whereIn('cart_id', $this->request->post('cart_id', ''))
                ->delete();
            return $formData;
        });
        return finish(0, '提交成功', ['order_sn' => $formData['order_sn']]);
    }
}
