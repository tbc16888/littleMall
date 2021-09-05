<?php
declare(strict_types=1);

namespace core\service\order;

use core\base\BaseService;
use core\exception\BusinessException;
use core\constant\order\OrderStatus;
use core\constant\order\OrderPayment;
use core\service\order\OrderOperationService;
use core\service\coupon\UserCouponService;
use core\service\shipping\ShippingService;

class OrderService extends BaseService
{

    protected string $table = 'order';
    protected string $tableUniqueKey = 'order_id';

    public function getList(array $params = [], int $page = 1, int $size = 10)
    {
        $field = 'o.*, u.account, u.nick_name';
        $field .= ', p.region_name as province_name, c.region_name as city_name';
        $field .= ', d.region_name as district_name, s.region_name as street_name';
        $instance = $this->db()->alias('o')->where($params)->field($field)
            ->leftJoin('user u', 'o.user_id = u.user_id')
            ->leftJoin('region p', 'p.region_code = o.province')
            ->leftJoin('region c', 'c.region_code = o.city')
            ->leftJoin('region d', 'd.region_code = o.district')
            ->leftJoin('region s', 's.region_code = o.street');
        if ($page == 1 && $size == 1) return $instance->find();
        return $instance->order('o.order_time', 'desc')
            ->page($page, $size)->select()->toArray();
    }

    /**
     * 取消订单
     * @param string $orderSn
     * @param string $orderGoodsId
     * @return OrderService
     */
    public function cancel(string $orderSn, $orderGoodsId = ''): self
    {
        static::transaction(function () use ($orderSn, $orderGoodsId) {
            $rData = [];
            $rData['cancel_time'] = date('Y-m-d H:i:s');
            $this->changeOrderStatus($orderSn, OrderStatus::CANCEL, $rData);
            OrderGoodsService::getInstance()->refund($orderSn, $orderGoodsId);
            $order = $this->db()->where('order_sn', $orderSn)->find();
            if ($order['user_coupon_id']) {
                UserCouponService::getInstance()->changeStatus(
                    $order['user_coupon_id'],
                    UserCouponService::NOT_USED,
                    ''
                );
            }
        });
        return $this;
    }

    /**
     * 支付
     * @param string $orderSn
     * @param array $payInfo
     * @return OrderService
     */
    public function payment(string $orderSn, array $payInfo = []): self
    {
        $default = [];
        $default['pay_type'] = OrderPayment::OFFLINE_PAY;
        $default['out_trade_no'] = '';
        $default['pay_time'] = date('Y-m-d H:i:s');
        $payInfo = array_merge($default, $payInfo);

        $rData = [];
        $rData['pay_status'] = 2;
        $rData['pay_type'] = $payInfo['pay_type'];
        $rData['out_trade_no'] = $payInfo['out_trade_no']; // 第三方订单
        $rData['pay_time'] = $payInfo['pay_time'];
        $this->changeOrderStatus($orderSn, OrderStatus::PAYMENT, $rData);

        return $this;
    }

    /**
     * 备货
     * @param string $orderSn
     * @return OrderService
     */
    public function prepare(string $orderSn): self
    {
        $this->changeOrderStatus($orderSn, OrderStatus::PREPARE);
        return $this;
    }

    /**
     * 发货
     * @param string $orderSn
     * @param $shippingId
     * @param string $shippingSn
     * @return OrderService
     */
    public function deliver(string $orderSn, $shippingId, string $shippingSn): self
    {
        $shippingInfo = ShippingService::getInstance()->dbQuery()
            ->where('shipping_id', $shippingId)->find();
        if (null === $shippingInfo) throw new BusinessException('运送公司不存在');

        $rData = [];
        $rData['shipping_name'] = $shippingInfo['shipping_name'];
        $rData['shipping_id'] = $shippingInfo['shipping_id'];
        $rData['shipping_sn'] = $shippingSn;
        $rData['shipping_time'] = date('Y-m-d H:i:s');
        $rData['shipping_status'] = 2;
        $this->changeOrderStatus($orderSn, OrderStatus::DELIVER, $rData);
        return $this;
    }

    /**
     * 完成
     * @param string $orderSn
     * @return OrderService
     */
    public function complete(string $orderSn): self
    {
        return $this->changeOrderStatus($orderSn, OrderStatus::COMPLETE);
    }


    /**
     * 退款
     * @param string $orderSn
     * @param int $refundMethod
     * @param float $refundAmount
     * @param string $reason
     * @return OrderService
     */
    public function refund(string $orderSn, int $refundMethod, float $refundAmount, string $reason = ''): self
    {
        $order = $this->db()->where('order_sn', $orderSn)->find();
        if (null === $order) throw new BusinessException('订单不存在或已删除');
        if ($order['order_amount'] < $refundAmount) {
            throw new BusinessException('退款金额大于订单金额');
        }

        // 修改状态
        $rData = [];
        $rData['refund_method'] = $refundMethod;
        $rData['refund_amount'] = $refundAmount;
        if ($order['pay_type'] == 1) $extendForm['refund_time'] = date('Y-m-d H:i:s');
        $this->changeOrderStatus($orderSn, OrderStatus::REFUNDED, $rData);

        // 原路退款
        if ($refundMethod == 1) {
            OrderPaymentService::getInstance()->refund($order,
                $order['order_amount']);
        }
        OrderGoodsService::getInstance()->refund($orderSn);
        return $this;
    }


    /**
     * 修改订单状态
     * @param string $orderSn
     * @param $action
     * @param array $update
     * @return OrderService
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function changeOrderStatus(string $orderSn, $action, array $update = []): self
    {
        $datetime = date('Y-m-d H:i:s');
        $order = $this->db()->where('order_sn', $orderSn)->find();
        if (null === $order) throw new BusinessException('订单不存在');

        if ($order['user_id'] !== $this->userId && !$this->adminId) {
            throw new BusinessException('用户异常');
        }

        $instance = $this->db()->where('order_sn', $orderSn);

        // 判断订单状态
        $isValidateSuccess = true;
        $orderStatus = $order['order_status'];
        if ($action === OrderStatus::CANCEL) {
            if ($orderStatus > OrderStatus::WAIT_PAYMENT) $isValidateSuccess = false;
            $instance->where('order_status', '<',
                OrderStatus::CANCEL);
        }

        // 支付
        if ($action === OrderStatus::PAYMENT) {
            // 待支付
            if ($orderStatus > OrderStatus::WAIT_PAYMENT) $isValidateSuccess = false;
            $instance->where('order_status', '<=',
                OrderStatus::WAIT_PAYMENT);
        }

        // 备货
        if ($action === OrderStatus::PREPARE) {
            // 未支付
            if ($orderStatus != OrderStatus::PAYMENT) $isValidateSuccess = false;
            $instance->where('order_status', OrderStatus::PAYMENT);
        }

        // 发货
        if ($action === OrderStatus::DELIVER) {

            // 未支付
            if ($orderStatus < OrderStatus::PAYMENT) $isValidateSuccess = false;
            // 已发货
            if ($orderStatus > OrderStatus::DELIVER) $isValidateSuccess = false;

            $instance->whereBetween('order_status', [
                OrderStatus::PAYMENT, OrderStatus::DELIVER
            ]);
        }

        // 确认（支付后可以直接完成）
        if ($action === OrderStatus::COMPLETE) {
            if ($orderStatus < OrderStatus::PAYMENT) $isValidateSuccess = false;
            $instance->where('order_status', '>',
                OrderStatus::PAYMENT);
            $update['complete_time'] = $datetime;
        }

        // 申请退款
        if ($action === OrderStatus::REFUNDING) {
            if ($orderStatus < OrderStatus::PAYMENT) $isValidateSuccess = false;
            if ($orderStatus == OrderStatus::REFUNDED) $isValidateSuccess = false;
            $instance->where('order_status', '>=',
                OrderStatus::PAYMENT)
                ->where('order_status', '<>', OrderStatus::REFUNDED);
        }

        if (false === $isValidateSuccess) {
            throw new BusinessException('订单状态错误');
        }

        // 执行操作
        $update['order_status'] = $action;
        \think\facade\Log::sql($instance->fetchSql(true)->limit(1)
            ->update($update));
        $result = $instance->limit(1)->update($update);
        if (!$result) throw new BusinessException('操作失败');

        // 订单记录
        OrderOperationService::getInstance()->add($orderSn, $action,
            $this->userId, $this->adminId);
        return $this;
    }


}