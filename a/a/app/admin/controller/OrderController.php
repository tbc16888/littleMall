<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\Request;
use core\base\BaseController;
use app\admin\validate\order\OrderValidate;
use core\constant\order\OrderStatus;
use core\service\order\OrderGoodsService;
use core\service\order\OrderFrontModuleService;
use core\service\order\OrderService;
use core\service\shipping\ShippingService;
use core\service\order\OrderOperationService;

class OrderController extends BaseController
{

    public function initialize()
    {
        OrderService::getInstance()->setAdminId(session('user_id'));
    }

    public function getQueryParams(Request $request): array
    {
        $condition = [];
        if (($orderSn = $request->get('order_sn', '', 'trim'))) {
            $condition['o.order_sn'] = $orderSn;
        }
        if (($orderStatus = intval($request->get('order_status', 0)))) {
            $condition['o.order_status'] = $orderStatus;
        }
        if (($payType = intval($request->get('pay_type', 0)))) {
            $condition['o.pay_type'] = $payType;
        }
        if (($payStatus = intval($request->get('pay_status', 0)))) {
            $condition['o.pay_status'] = $payStatus;
        }
        if (($userId = $request->get('user_id', '', 'trim'))) {
            $condition['o.user_id'] = $userId;
        }
        if (($goodsId = $request->get('goods_id', '', 'trim'))) {
            $condition['og.goods_id'] = $goodsId;
        }
        if (($goodsSkuId = $request->get('goods_sku_id', '', 'trim'))) {
            $condition['og.goods_sku_id'] = $goodsSkuId;
        }
        if ($request->has('is_comment')) {
            $condition['o.order_status'] = Db::raw('> 7');
            $condition['o.is_comment'] = 0;
        }
        if (($outTradeNo = $request->get('out_trade_no', ''))) {
            $condition['o.out_trade_no'] = $outTradeNo;
        }
        if (($orderTime1 = $request->get('order_time_1', ''))) {
            $condition['o.order_time'] = Db::raw("> '{$orderTime1}'");;
        }
        if (($orderTime2 = $request->get('order_time_2', ''))) {
            $condition['o.order_time'] = Db::raw("< '{$orderTime2}'");;
        }
        if ($orderTime1 && $orderTime2) {
            $condition['o.order_time'] = Db::raw("between '{$orderTime1}' and '{$orderTime2}'");;
        }
        if (($payTime1 = $request->get('pay_time_1', ''))) {
            $condition['o.pay_time'] = Db::raw("> '{$payTime1}'");;
        }
        if (($payTime2 = $request->get('pay_time_2', ''))) {
            $condition['o.pay_time'] = Db::raw("< '{$payTime2}'");;
        }
        if ($payTime1 && $payTime2) {
            $condition['o.pay_time'] = Db::raw("between '{$payTime1}' and '{$payTime2}'");;
        }
        if (($shippingTime1 = $request->get('shipping_time_1', ''))) {
            $condition['o.shipping_time'] = Db::raw("> '{$shippingTime1}'");;
        }
        if (($shippingTime2 = $request->get('shipping_time_2', ''))) {
            $condition['o.shipping_time'] = Db::raw("< '{$shippingTime2}'");;
        }
        if ($shippingTime1 && $shippingTime2) {
            $condition['o.shipping_time'] = Db::raw("between '{$shippingTime1}' and '{$shippingTime2}'");;
        }
        if (($completeTime1 = $request->get('complete_time_1', ''))) {
            $condition['o.complete_time'] = Db::raw("> '{$completeTime1}'");;
        }
        if (($completeTime2 = $request->get('complete_time_2', ''))) {
            $condition['o.complete_time'] = Db::raw("< '{$completeTime2}'");;
        }
        if ($completeTime1 && $completeTime2) {
            $condition['o.complete_time'] = Db::raw("between '{$completeTime1}' and '{$completeTime2}'");;
        }
        return $condition;
    }

    /**
     * 列表
     * @RequestMapping(path="/index", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        $condition = $this->getQueryParams($request);
        $service = OrderService::getInstance();
        $total = $service->db()->alias('o')->where($condition)->count();
        $lists = $service->getList($condition, $this->page(), $this->size());
        $order = [];
        $moduleService = new OrderFrontModuleService();
        foreach ($lists as $item) {
            $moduleItem = [];
            $moduleList = $moduleService->clear()->setOrder($item)
                ->basic()->status()->goods()->user()
                ->backstageOperation()->getModule();
            foreach ($moduleList as $module) $moduleItem[$module['type']] = $module;
            $order[] = $moduleItem;
        }
        return finish(0, '获取成功', ['total' => $total,
            'list' => $order]);
    }

    /**
     * 订单商品
     * @RequestMapping(path="/goods", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function goods(Request $request)
    {
        $condition = [];
        $condition = Order::getInstance()->getQueryParams($request);
        $total = OrderGoods::getInstance()->getTotal($condition);
        $lists = OrderGoods::getInstance()->getList($condition,
            intval($request->get('page', 1)), intval($request->get('size', 10)));
        foreach ($lists as &$order) {
            $order['order_status_text'] = Order::$orderStatusMap[$order['order_status']];
        }
        return finish(0, '获取成功', ['total' => $total, 'list' => $lists]);
    }


    /**
     * 订单详情
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($orderSn = $request->get('order_sn', ''))) {
            return finish(1, '参数错误');
        }

        $condition = [];
        $condition['o.order_sn'] = $orderSn;
        $order = OrderService::getInstance()->getList($condition, 1, 1);
        if (null === $order) return finish(1, '订单不存在');

        $response = [];
        $moduleService = new OrderFrontModuleService();
        $moduleList = $moduleService->clear()->setOrder($order)
            ->basic()->status()->goods()->address()->charges()->user()
            ->backstageOperation()->getModule();
        foreach ($moduleList as $item) $response[$item['type']] = $item;
        return finish(0, '获取成功', $response);
    }

    /**
     * 修改
     * @RequestMapping(path="/edit", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function edit(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $orderInfo = OrderService::getInstance()->db()
            ->where('order_sn', $orderSn)->find();
        if (null === $orderInfo) return finish(1, '订单不存在');

        //
        $orderAmount = floatval($request->post('order_amount'));
        $rData['change_amount'] = $orderInfo['order_amount'] - $orderAmount;

        $rData = [];
        $rData['order_amount'] = $orderAmount;
        $rData['seller_remark'] = $request->post('seller_remark', '', 'trim');
        $dispute = ['contact', 'contact_number', 'province', 'city', 'district', 'address', 'gender', 'house_number'];
        $addressData = $request->getPostParams($dispute);
        $this->validate($addressData, OrderValidate::class);
        $dispute = array_flip($dispute);
        $address = array_intersect_key($addressData, $dispute);
        $rData = array_merge($rData, $address);
        OrderService::getInstance()->setFormData($rData)
            ->addOrUpdate($orderInfo['order_id']);
        OrderOperationService::getInstance()->add($orderSn, OrderStatus::EDIT,
            '', session('user_id'));
        return finish(0, '修改成功');
    }

    /**
     * 取消
     * @RequestMapping(path="/cancel", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function cancel(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        OrderService::getInstance()->cancel($orderSn);
        return finish(0, '操作成功');
    }

    /**
     * 删除
     * @RequestMapping(path="/delete", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        OrderService::getInstance()->softDelete($orderSn);
        return finish(0, '操作成功');
    }

    /**
     * 支付
     * @RequestMapping(path="/payment", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function payment(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $payInfo = ['out_trade_no' =>
            $request->post('out_trade_no', '')];
        OrderService::getInstance()->payment($orderSn, $payInfo);
        return finish(0, '操作成功');
    }

    /**
     * 备货
     * @RequestMapping(path="/prepare", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function prepare(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        OrderService::getInstance()->prepare($orderSn);
        return finish(0, '操作成功');
    }

    /**
     * 发货
     * @RequestMapping(path="/deliver", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function deliver(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        if (!($shippingId = $request->post('shipping_id', ''))) {
            return finish(1, '运送公司未选择');
        }
        $shippingSn = $request->post('shipping_sn', '');
        OrderService::getInstance()->deliver($orderSn, $shippingId, $shippingSn);
        return finish(0, '操作成功');
    }

    /**
     * 确认收货｜完成订单
     * @RequestMapping(path="/complete", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function complete(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        OrderService::getInstance()->complete($orderSn);
        return finish(0, '操作成功');
    }

    /**
     * 退款
     * @RequestMapping(path="/refund", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function refund(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $refundMethod = intval($request->post('refund_method', 1));
        $refundAmount = floatval($request->post('refund_amount'));
        $reason = $request->post('reason', '', 'trim');
        OrderService::getInstance()->refund($orderSn, $refundMethod,
            $refundAmount, $reason);
        return finish(0, '操作成功');
    }
}
