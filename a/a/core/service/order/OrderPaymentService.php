<?php
declare(strict_types=1);

namespace core\service\order;

use EasyWeChat\Factory;
use core\base\BaseService;
use core\exception\BusinessException;
use core\constant\order\OrderStatus;

class OrderPaymentService extends BaseService
{
    const WAIT_PAY = 0;
    const OFFLINE_PAY = 1;
    const ALIPAY_APP = 2;
    const WECHAT_MINI_PROGRAM = 3;
    const BALANCE = 4;

    public static array $typeMapper = [
        self::WAIT_PAY => '',
        self::OFFLINE_PAY => '线下支付',
        self::ALIPAY_APP => '支付宝',
        self::WECHAT_MINI_PROGRAM => '微信小程序',
        self::BALANCE => '账户余额'
    ];

    public function checkAndGetInfo($orderSn)
    {
        $condition = [];
        $condition['order_sn'] = $orderSn;
        $orderInfo = OrderService::getInstance()->dbQuery($condition)->find();
        if (null === $orderInfo) return finish(1, '订单不存在');
        if ($orderInfo['order_status'] > OrderStatus::CANCEL) {
            throw new BusinessException('订单状态错误');
        }
        if (strtotime($orderInfo['pay_deadline_time']) - time() < 0) {
            throw new BusinessException('超过支付有效截至时间');
        }
        return $orderInfo;
    }


    public function getEasyWeChatWxPaymentApp(): \EasyWeChat\Payment\Application
    {
        return Factory::payment([
            'app_id' => env('wx.mini_program_app_id'),
            'mch_id' => env('wx.pay_mch_id'),
            'key' => env('wx.pay_key'),
            'cert_path' => env('wx.pay_cert_path'),
            'key_path' => env('wx.pay_key_path'),
        ]);
    }
}