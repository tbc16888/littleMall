<?php
declare(strict_types=1);

namespace app\v1\controller;

use app\Request;
use app\v1\validate\OrderRefundValidate;
use core\base\BaseController;
use core\constant\order\OrderStatus;
use core\service\order\OrderFrontModuleService;
use core\service\order\OrderModuleService;
use core\service\order\OrderPaymentService;
use core\service\order\OrderRefundService;
use core\service\order\OrderService;
use think\facade\Db;

class OrderController extends BaseController
{
    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    protected function initialize()
    {
        OrderService::getInstance()->setUserId(session('user_id'));
    }

    /**
     * 订单列表
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        $condition['o.user_id'] = session('user_id');
        $condition['o.is_delete'] = 0;
        if (($tab = $request->get('tab', ''))) {
            if ($tab === 'waitPay') {
                $condition['o.order_status'] =
                    Db::raw("< " . OrderStatus::CANCEL);
            }
            if ($tab === 'waitSend') {
                $status = implode(',', [
                    OrderStatus::PAYMENT, OrderStatus::PREPARE
                ]);
                $condition['o.order_status'] = Db::raw("in ($status)");
            }
            if ($tab === 'waitComment') {
                $condition['o.order_status'] =
                    Db::raw(">= " . OrderStatus::COMPLETE);
                $condition['o.is_comment'] = 0;
            }
            if ($tab === 'waitConfirm') {
                $condition['o.order_status'] = OrderStatus::DELIVER;
            }
        }
        $service = OrderService::getInstance();
        $total = $service->dbQuery($condition, 'o')->count();
        $lists = $service->getList($condition, $this->page(), $this->size());
        $order = [];

        $moduleService = new OrderFrontModuleService();

        foreach ($lists as $item) {
            $moduleItem = [];
            $moduleList = $moduleService->clear()->setOrder($item)
                ->basic()->status()->goods()
                ->frontOperation()->getModule();
            foreach ($moduleList as $module) $moduleItem[$module['type']] = $module;
            $order[] = $moduleItem;
        }
        return finish(0, '获取成功', ['total' => $total,
            'list' => $order]);
    }

    /**
     * 统计
     * @RequestMapping(path="/statistics", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function statistics(Request $request)
    {
        $waitPay = OrderStatistics::getInstance()->getWaitPayTotal();
        $waitSend = OrderStatistics::getInstance()->getWaitSend();
        $waitComment = OrderStatistics::getInstance()->getWaitComment();
        $waitConfirmReceipt = OrderStatistics::getInstance()->getWaitConfirmReceipt();
        return finish(0, '获取成', ['wait_pay' => $waitPay, 'wait_send' => $waitSend,
            'wait_confirm_receipt' => $waitConfirmReceipt, 'wait_comment' => $waitComment]);
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
            ->basic()->status()->goods()->countdown()->address()
            ->logistics()->charges()->frontOperation()->getModule();
        foreach ($moduleList as $item) $response[$item['type']] = $item;

        // 订单信息
        $info = [];
        $info['type'] = 'info';
        $infoList = ['order_sn' => '订单编号', 'order_time' => '下单时间', 'pay_time' => '支付时间', 'cancel_time' => '取消时间', 'shipping_time' => '发货时间', 'complete_time' => '完成时间', 'buyer_remark' => '用户备注'];
        foreach ($infoList as $key => $label) {
            if ($order[$key] === '0000-00-00 00:00:00' || !$order[$key]) continue;
            $info['data']['list'][] = ['label' => $label,
                'text' => $order[$key], 'highlight' => 0, 'copy' => 0];
        }
        if ($order['pay_type']) {
            $info['data']['list'][] = ['label' => '支付方式',
                'text' => OrderPaymentService::$typeMapper[$order['pay_type']]];
            $info['data']['list'][] = ['label' => '支付流水',
                'text' => $order['out_trade_no']];
        }

        $response['info'] = $info;
        return finish(0, '获取成功', $response);
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
        }return finish(0, '操作成功');
        OrderService::getInstance()->softDelete($orderSn);
        return finish(0, '操作成功');
    }


    /**
     * 申请退款
     * @RequestMapping(path="/refund", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function refund(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        // OrderRefundService::getInstance()->apply($orderSn);
        $refund = json_decode($request->post('refund', '[]'), true);
        if (!count($refund)) return finish(1, '参数错误');
        foreach ($refund as $item) {
            $this->validate($item, OrderRefundValidate::class);
        }
        foreach ($refund as $item) {
            OrderRefundService::getInstance()
                ->apply($orderSn, $item['order_goods_id'], $item);
        }
        return finish(0, '申请成功');
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
     * 物流信息
     * @RequestMapping(path="/logistics", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function logistics(Request $request)
    {
        if (!($orderSn = $request->get('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $condition = [];
        $condition['order_sn'] = $orderSn;
        $orderInfo = OrderService::getInstance()->dbQuery($condition)->find();
//        $lists = OrderExt::getInstance()->getLogisticsList($orderSn);
        $lists = [
//            ['text' => '暂时还没有快递信息', 'time' => date('Y-m-d H:i:s')]
        ];
//        for ($i = 0; $i < 10; $i++) {
//            $lists[] = ['text' => '您的快件代签收（本人），如有疑问请电联小哥【黄典文，电话：150****3538】。疫情期间顺丰每日对网点消毒、小哥每日测温、配戴口罩，感谢您使用顺丰，期待再次为您服务。', 'time' => date('Y-m-d H:i:s')];
//        }
        $lists[] = [
            'text' => '您的订单已签收，签收人（本人签收）。',
            'time' => date('Y-m-d H:i:s')
        ];
        $lists[] = [
            'text' => '订单正在派送中，请保持电话畅通，快递员（仰泳的鱼，电话 13960000001）。',
            'time' => date('Y-m-d H:i:s')
        ];
        $lists[] = [
            'text' => '快件到达某某营业点',
            'time' => date('Y-m-d H:i:s')
        ];

        $lists[] = [
            'text' => '快件在【某某中转场】完成分拣，准备发往【某某中专场】',
            'time' => date('Y-m-d H:i:s')
        ];
        $lists[] = [
            'text' => '快件在【某某营业点】完成分拣，准备发往【某某中转场】',
            'time' => date('Y-m-d H:i:s')
        ];
        $lists[] = [
            'text' => '顺丰快递已收取快件',
            'time' => date('Y-m-d H:i:s')
        ];
        return finish(0, '获取成功', ['list' => $lists, 'info' => [
            ['label' => '承运公司', 'text' => '顺丰快递'],
            ['label' => '联系电话', 'text' => '11183'],
            ['label' => '运单号', 'text' => 'SF999821983', 'copy' => 1]
        ]]);
    }
}