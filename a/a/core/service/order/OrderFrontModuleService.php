<?php
declare(strict_types=1);

namespace core\service\order;

use core\base\BaseFrontModule;
use core\constant\order\OrderStatus;
use core\service\user\UserAddressService;

class OrderFrontModuleService extends BaseFrontModule
{
    protected array $order = [];

    protected array $module = [];

    protected array $orderGoodsList = [];


    // 设置订单信息
    public function setOrder(array $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function clear(): self
    {
        $this->module = [];
        return $this;
    }

    public function getModule(): array
    {
        return $this->module;
    }

    // 基本信息模块
    public function basic(): self
    {
        $item = [];
        $item['type'] = 'basic';
        $item['data'] = [];
        $item['data']['field'] = array_intersect_key($this->order, array_flip([
            'order_sn', 'order_time', 'order_amount', 'cancel_time', 'pay_time',
            'seller_remark', 'buyer_remark', 'refund_time', 'complete_time',
            'shipping_time', 'goods_amount'
        ]));
        $this->module[] = $item;
        return $this;
    }

    // 订单用户信息
    public function user(): self
    {
        $item = [];
        $item['type'] = 'user';
        $item['data']['field'] = array_intersect_key($this->order, array_flip([
            'user_id', 'nick_name', 'avatar'
        ]));
        $this->module[] = $item;
        return $this;
    }

    // 订单状态信息
    public function status(): self
    {
        $item = [];
        $item['type'] = 'status';
        $item['data'] = [];
        $item['data']['field']['text'] =
            OrderStatus::$statusMapper[$this->order['order_status']];
        $item['data']['field']['note'] = '';
        $item['data']['style']['color'] = '#333333';
        $item['data']['style']['background'] = '#409eff';
        $item['data']['style']['background'] = '#1DBD61';
        $item['data']['style']['background'] = '#e93323';
        $this->module[] = $item;
        return $this;
    }

    // 订单商品
    public function goods($orderGoodsId = ''): self
    {
        $item = [];
        $item['type'] = 'goods';
        $item['data'] = [];
        $dataList = OrderGoodsService::getInstance()
            ->getOrderGoods($this->order['order_sn'], $orderGoodsId);
        $this->orderGoodsList = $dataList;
        foreach ($dataList as &$goods) {
            $goods['operation'] = [];
            $section = [OrderStatus::REFUNDING, OrderStatus::REFUNDED];
            if ($this->order['order_status'] > OrderStatus::PAYMENT && !in_array($this->order['order_status'], $section) && $goods['refund_status'] == 0) {
//                $goods['operation'][] = ['label' => '加入购物车', 'code' => 'refund'];
                $goods['operation'][] = ['label' => '申请售后', 'code' => 'service'];
            }
            if ($this->order['order_status'] === OrderStatus::PAYMENT && $goods['refund_status'] == 0) {
                $goods['operation'][] = ['label' => '申请退款', 'code' => 'refund'];
            }
        }
        $item['data']['list'] = $dataList;
        $this->module[] = $item;
        return $this;
    }

    // 收货地址
    public function address(): self
    {
        $item = [];
        $item['type'] = 'address';
        $item['data']['icon'] = '';
        $field = array_intersect_key($this->order, array_flip([
            'contact', 'contact_number', 'city', 'province', 'district',
            'gender', 'province_name', 'city_name', 'district_name', 'street_name',
            'address', 'house_number', 'longitude', 'latitude']));
        $field['full_address'] = UserAddressService::getFullAddressField($field);
        $item['data']['field'] = $field;
        $this->module[] = $item;
        return $this;
    }

    // 费用明细
    public function charges(): self
    {
        $charges = [];
        $charges['type'] = 'charges';
        $items = [
            ['label' => '商品总额', 'field' => 'goods_amount', 'symbol' => '¥'],
            ['label' => '运费', 'field' => 'shipping_fee', 'symbol' => '+¥'],
            ['label' => '优惠券', 'field' => 'coupon_amount', 'symbol' => '-¥'],
            ['label' => '积分抵扣', 'field' => 'integral_amount', 'symbol' => '-¥'],
            ['label' => '实付款', 'field' => 'order_amount', 'symbol' => '¥', 'highlight' => 1]
        ];
        foreach ($items as $item) {
            // if ($this->order[$item['field']] === '0.00') continue;
            $charges['data']['list'][] = [
                'label' => $item['label'],
                'text' => $item['symbol'] . $this->order[$item['field']],
                'highlight' => $item['highlight'] ?? 0
            ];
        }
        $this->module[] = $charges;
        return $this;
    }

    // 物流信息
    public function logistics(): self
    {
        $logistics = [];
        $logistics['type'] = 'logistics';
        $logistics['show'] = 0;
        $logistics['data'] = [];
        $logistics['data']['field'] = [];
        $logistics['data']['field']['text'] = '暂无记录';
//        $logistics['data']['field']['text'] = '您的快件代签收（本人），如有疑问请电联小哥【黄典文，电话：15080073538】。疫情期间顺丰每日对网点消毒、小哥每日测温、配戴口罩，感谢您使用顺丰，期待再次为您服务。';
        $logistics['data']['field']['time'] = date('Y-m-d H:i:s');
        $this->module[] = $logistics;
        return $this;
    }

    // 倒计时操作
    public function countdown(): self
    {
        $countdown = [];
        $countdown['type'] = 'countdown';
        $countdown['data']['field'] = [];
        if ($this->order['order_status'] == OrderStatus::WAIT_PAYMENT) {
            $countdown['data']['field']['second'] =
                strtotime($this->order['pay_deadline_time']) - time();
        }
//        $countdown['data']['field']['second'] = 0;
        $this->module[] = $countdown;
        return $this;
    }

    // 前台用户操作
    public function frontOperation(): self
    {
        $operation = [];
        $operation['type'] = 'operation';
        $order = $this->order;
        $list = [];
        if ($order['order_status'] === OrderStatus::WAIT_PAYMENT) {
            $list[] = ['label' => '立即支付', 'code' => 'payment'];
            $list[] = ['label' => '取消订单', 'code' => 'cancel'];
        }

        if ($order['order_status'] === OrderStatus::CANCEL) {
            $list[] = ['label' => '删除订单', 'code' => 'delete', 'color' => '#F30',
                'border' => '#F30', 'background' => '#FFF'];
        }
        if ($order['order_status'] === OrderStatus::REFUNDED) {
            $list[] = ['label' => '删除订单', 'code' => 'delete'];
        }
        if (in_array($order['order_status'], [OrderStatus::PAYMENT, OrderStatus::PREPARE])){
            if (!count($this->orderGoodsList)) $this->goods();
            $showAllRefund = true;
            foreach ($this->orderGoodsList as $goods) {
                if ($goods['refund_status'] !== 0) $showAllRefund = false;
            }
            if ($showAllRefund) $list[] = ['label' => '申请退款', 'code' => 'refund'];
        }

        if ($order['shipping_status'] > 1) {
            $list[] = ['label' => '查看物流', 'code' => 'logistics'];
        }

        if ($order['order_status'] === OrderStatus::DELIVER) {
            $list[] = ['label' => '确认收货', 'code' => 'confirm'];
        }

        if ($order['order_status'] === OrderStatus::COMPLETE) {
//            $list[] = ['label' => '申请售后', 'code' => 'service'];
            if (!$order['is_comment']) {
                $list[] = ['label' => '去评价', 'code' => 'comment',];
            }
        }
        $operation['data']['list'] = $list;
        $this->module[] = $operation;
        return $this;
    }

    // 后台操作
    public function backstageOperation(): self
    {
        $order = $this->order;
        $operation = [];
        $operation['type'] = 'operation';
        $list = [];
        if ($order['order_status'] === OrderStatus::WAIT_PAYMENT) {
            $list[] = ['label' => '修改', 'code' => 'modify'];
            $list[] = ['label' => '支付', 'code' => 'payment'];
            $list[] = ['label' => '取消', 'code' => 'cancel'];
        }
        if ($order['order_status'] === OrderStatus::PAYMENT) {
            $list[] = ['label' => '修改', 'code' => 'modify'];
            $list[] = ['label' => '备货', 'code' => 'prepare'];
            $list[] = ['label' => '发货', 'code' => 'deliver'];
            $list[] = ['label' => '退款', 'code' => 'refund'];
        }
        if ($order['order_status'] === OrderStatus::PREPARE) {
            $list[] = ['label' => '修改', 'code' => 'modify'];
            $list[] = ['label' => '发货', 'code' => 'deliver'];
            $list[] = ['label' => '退款', 'code' => 'refund'];
        }
        if ($order['order_status'] === OrderStatus::DELIVER) {
            $list[] = ['label' => '确认', 'code' => 'complete'];
            $list[] = ['label' => '退款', 'code' => 'refund'];
        }
        if ($order['order_status'] === OrderStatus::REFUNDING) {
            $list[] = ['label' => '退款审核', 'code' => 'audit'];
        }
        $operation['data']['list'] = $list;
        $this->module[] = $operation;
        return $this;
    }
}