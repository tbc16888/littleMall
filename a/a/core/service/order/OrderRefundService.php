<?php
declare(strict_types=1);

namespace core\service\order;

use core\base\BaseService;
use core\constant\order\OrderStatus;
use core\exception\BusinessException;
use core\service\user\UserBalanceService;

class OrderRefundService extends BaseService
{
    const AUDIT_WAIT = 1;
    const AUDIT_PASS = 2;
    const AUDIT_FAIL = 3;

    // 信息配置
    protected string $table = 'order_refund';
    protected string $tableUniqueKey = 'refund_id';

    // 申请退款
    public function apply($orderSn, array $data, $orderGoodsId = ''): self
    {
        $condition = [];
        $condition['order_sn'] = $orderSn;
        $condition['order_goods_id'] = $orderGoodsId;
        $condition['audit_status'] = self::AUDIT_WAIT;
        $info = $this->dbQuery($condition)->order('id', 'desc')->find();
        if (null !== $info) throw new BusinessException('申请已提交', 1);

        $condition = [];
        $condition['order_sn'] = $orderSn;
        $order = OrderService::getInstance()->dbQuery($condition)->find();
        if (null === $order) throw new BusinessException('数据错误');
        if (!isset($data['amount'])) $data['amount'] = $order['order_amount'];

        if ($orderGoodsId) {
            $condition['order_goods_id'] = $orderGoodsId;
            $orderGoodsInfo = OrderGoodsService::getInstance()->dbQuery($condition)
                ->find();
            if ($orderGoodsInfo['refund_status'] !== 0) {
                throw new BusinessException('状态错误');
            }
            if ($data['amount'] > $orderGoodsInfo['goods_amount']) {
                throw new BusinessException('退款金额错误');
            }
        }

        static::transaction(function () use ($order, $data, $orderGoodsId) {

            $rData = [];
            $rData['refund_sn'] = static::buildSnowflakeId();
            $rData['audit_status'] = self::AUDIT_WAIT;
            $rData['order_sn'] = $order['order_sn'];
            $rData['refund_amount'] = $data['amount'];
            $rData['description'] = $data['description'];
            $rData['reason'] = $data['reason'];
            $rData['order_status'] = $order['order_status'];
            $rData['order_goods_id'] = $orderGoodsId;
            $this->setFormData($rData)->addOrUpdate();

            if ($orderGoodsId) {
                $condition = [];
                $condition['order_goods_id'] = $orderGoodsId;
                $result = OrderGoodsService::getInstance()->dbQuery($condition)
                    ->where($condition)->update(['refund_status' => 1]);
                if (!$result) throw new BusinessException('申请失败');
                OrderService::getInstance()->dbQuery()
                    ->where('order_sn', $order['order_sn'])->update(['refund_status' => 1]);
            } else {
                OrderService::getInstance()->changeOrderStatus($order['order_sn'],
                    OrderStatus::REFUNDING);
            }
        });

        return $this;
    }

    // 退款审核
    public function audit(string $refundId, int $status, int $refundMethod, float $refundAmount, string $reason = ''): self
    {
        $condition = [];
        $condition['refund_id'] = $refundId;
        $refund = $this->db()->where($condition)->find();
        if (null === $refund) throw new BusinessException('没有可审核记录');
        if ($refund['audit_status'] != self::AUDIT_WAIT) {
            throw new BusinessException('退款已审核');
        }

        static::transaction(function () use ($status, $refundMethod, $refundAmount, $refund, $reason) {
            $status = $status + 1;
            $rData = [];
            $rData['audit_status'] = $status;
            $rData['audit_remark'] = $reason;
            $rData['audit_time'] = date('Y-m-d H:i:s');
            $condition = [];
            $condition['order_sn'] = $refund['order_sn'];
            $condition['refund_id'] = $refund['refund_id'];
            $condition['audit_status'] = self::AUDIT_WAIT;
            if (!$this->db()->where($condition)->limit(1)->update($rData)) {
                throw new BusinessException('审核失败[1001]', 1);
            }

            // 商品的退款状态
            if ($refund['order_goods_id']) {
                $refundStatus = $status == self::AUDIT_PASS ? 2 : 0;
                $condition = [];
                $condition['order_goods_id'] = $refund['order_goods_id'];
                $condition['refund_status'] = 1;
                $result = OrderGoodsService::getInstance()->db()->where($condition)
                    ->limit(1)->update(['refund_status' => $refundStatus]);
                if (!$result) throw new BusinessException('操作失败');
            }

            // 账户余额
            $order = OrderService::getInstance()->db()
                ->where('order_sn', $refund['order_sn'])->find();


            // 通过
            if ($status == self::AUDIT_PASS) {
                $allRefund = true;
                if ($refund['order_goods_id']) {
                    $orderGoodsList = OrderGoodsService::getInstance()->db()
                        ->where('order_sn', $refund['order_sn'])->select()->toArray();
                    foreach ($orderGoodsList as $item) {
                        if ($item['refund_status'] !== 2) $allRefund = false;
                    }

                    OrderPaymentService::getInstance()->refund($order,
                        $refund['refund_amount']);

//                    if ($order['pay_type'] === OrderPaymentService::BALANCE) {
//                        UserBalanceService::getInstance()->change($order['user_id'],
//                            floatval($refund['refund_amount']),
//                            UserBalanceService::CONSUME_REFUND,
//                            '商品退款' . $order['order_sn']);
//                    }

                    if ($allRefund) {
                        OrderService::getInstance()
                            ->setAdminId($this->getAdminId())
                            ->changeOrderStatus($order['order_sn'],
                                OrderStatus::REFUNDED);
                    }
                } else {
                    OrderService::getInstance()
                        ->setAdminId($this->getAdminId())
                        ->refund($order['order_sn'], $refundMethod,
                            $refundAmount, $reason);
                }
            }

            // 拒绝，如果是订单退款改回订单状态
            if ($status == self::AUDIT_FAIL && !$refund['order_goods_id']) {
                $condition = [];
                $condition['order_sn'] = $refund['order_sn'];
                $condition['order_status'] = OrderStatus::REFUNDING;
                $rData = [];
                $rData['order_status'] = $refund['order_status'];
                $instance = OrderService::getInstance()->db()->where($condition);
                if (!$instance->limit(1)->update($rData)) {
                    throw new BusinessException('审核失败', 1);
                }
            }

            if ($status == self::AUDIT_FAIL && $refund['order_goods_id']) {
                $total = OrderGoodsService::getInstance()->db()
                    ->where('order_sn', $refund['order_sn'])
                    ->where('refund_status', '>', 0)->count();
                OrderService::getInstance()->dbQuery()
                    ->where('order_sn', $refund['order_sn'])
                    ->update(['refund_status' => $total]);
            }
        });
        return $this;
    }


    // 退款
    public function refund($orderSn)
    {

    }


    // 整体申请退款
    public function applyWholeOrder(string $orderSn)
    {
        $condition = [];
        $condition['order_sn'] = $orderSn;
        $condition['audit_status'] = self::AUDIT_WAIT;
        $info = $this->dbQuery($condition)->order('id', 'desc')->find();
        if (null !== $info) throw new BusinessException('申请已提交', 1);

        $condition = [];
        $condition['order_sn'] = $orderSn;
        $order = OrderService::getInstance()->dbQuery($condition)->find();
        if (null === $order) throw new BusinessException('数据错误');
        if (!isset($data['amount'])) $data['amount'] = $order['order_amount'];

        static::transaction(function () use ($order, $data) {

            $rData = [];
            $rData['refund_sn'] = static::buildSnowflakeId();
            $rData['audit_status'] = self::AUDIT_WAIT;
            $rData['order_sn'] = $order['order_sn'];
            $rData['refund_amount'] = $data['amount'];
            $rData['order_status'] = $order['order_status'];
            $this->setFormData($rData)->addOrUpdate();
            OrderService::getInstance()->changeOrderStatus($order['order_sn'],
                OrderStatus::REFUNDING);
        });
        return $this;
    }


}