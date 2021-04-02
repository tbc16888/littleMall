<?php
declare(strict_types=1);

namespace core\constant\order;

class OrderStatus
{
    const WAIT_SELLER_CONFIRM = 1000;
    const WAIT_PAYMENT = 1100;
    const CANCEL = 1200;
    const PAYMENT = 1300;
    const PREPARE = 1400;
    const SORTING = 1401;
    const DELIVER = 1490;
    const COMPLETE = 9100;
    const RECHARGE = 9001;
    const REFUNDING = 1500;
    const REFUNDED = 1510;
    const EDIT = 10000;
    public static array $statusMapper = [
        self::WAIT_SELLER_CONFIRM => '等待商家确认',
        self::WAIT_PAYMENT => '待支付',
        self::CANCEL => '已取消',
        self::PAYMENT => '已支付',
        self::PREPARE => '备货中',
        self::SORTING => '分拣中',
        self::DELIVER => '已发货',
        self::REFUNDING => '退款中',
        self::REFUNDED => '已退款',
        self::COMPLETE => '交易完成',
        self::RECHARGE => '充值成功',
    ];

    public static array $actionMapper = [
        self::CANCEL => '取消',
        self::PAYMENT => '支付',
        self::PREPARE => '备货',
        self::SORTING => '分拣',
        self::DELIVER => '发货',
        self::COMPLETE => '确认',
        self::REFUNDING => '申请退款',
        self::REFUNDED => '退款',
        self::EDIT => '编辑'
    ];

}