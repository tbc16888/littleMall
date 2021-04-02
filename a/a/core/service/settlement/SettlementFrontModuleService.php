<?php
declare(strict_types=1);

namespace core\service\settlement;

use core\service\user\UserAddressService;
use core\service\user\UserIntegralService;
use core\service\coupon\UserCouponService;
use core\service\goods\GoodsIntegralService;
use core\service\cart\CartFrontModuleService;

class SettlementFrontModuleService
{

    public function submit(): array
    {
        $submit = [];
        $submit['type'] = 'submit';
        $submit['data'] = [];
        $submit['data']['field'] = [];
        $submit['data']['field']['note'] = '';
        $submit['data']['field']['text'] = '';
        $submit['data']['style']['background'] = '#d81e06';
        return $submit;
    }


    public function goods(array $goodsList): array
    {
        $module = [];
        $module['type'] = 'goods';
        $items = (new CartFrontModuleService())->items($goodsList);
        $module['data'] = $items['data'];
        return $module;
    }

    public function address($addressId = ''): array
    {
        $module = [];
        $module['type'] = 'address';
        $data = [];
        $address = UserAddressService::getInstance()->getByIdOrDefault($addressId);
        if (null === $address) $address = ['address_id' => ''];
        $data['field'] = $address;
        $data['form'] = ['address_id' => $address['address_id']];
        $data['show'] = 1;
        $module['data'] = $data;
        return $module;
    }

    public function deliveryTime($deliveryTime = ''): array
    {
        $module = [];
        $module['show'] = 0;
//        $module['list'] = Delivery::getInstance()->getTimelyDeliveryTimeSection();
        $module['form'] = ['delivery_time' => $deliveryTime];
        $module['field'] = ['text' => '尽快送达'];
        return $module;
    }

    public function integral(array $goodsList, $integral): array
    {
        $module = [];
        $module['type'] = 'integral';
        $data = [];
        $userLeftIntegral = UserIntegralService::getInstance()
            ->current(session('user_id'));
        $integralSummary = GoodsIntegralService::getInstance()->getGoodsListIntegralInfo($goodsList);

        $discountIntegral = $integralSummary[GoodsIntegralService::INTEGRAL_OR_CASH];
        $mustBeUseIntegral = array_sum($integralSummary) - $discountIntegral;
        $leftIntegral = $userLeftIntegral - $mustBeUseIntegral;

        // 最多可抵扣
        $maxDiscount = $leftIntegral;
        if ($maxDiscount > $discountIntegral) $maxDiscount = $discountIntegral;
        if ($maxDiscount < 0) $maxDiscount = 0;
        if ($integral > $maxDiscount) $integral = $maxDiscount;
        if ($integral < 100) $integral = 0;
        $data['field'] = [];
        $data['field']['user'] = $userLeftIntegral;
        $data['field']['note'] = '最多可抵扣 ¥' . number_format($maxDiscount / 100, 2, '.', '');
        $data['field']['discount'] = $maxDiscount;
        $data['field']['discount_amount'] = $integral / 100;
        $data['field']['integral'] = $mustBeUseIntegral;

        $data['switch']['disabled'] = 0;
        $data['switch']['checked'] = 0;
        $data['switch']['color'] = '#f30';
        if ($userLeftIntegral < 100) {
            $data['field']['note'] = '当前积分' . $userLeftIntegral . '，满100可用';
            if ($userLeftIntegral < 1) $data['field']['note'] = '暂无积分可用';
            $data['switch']['disabled'] = 1;
        }

        $data['form'] = ['integral' => $integral];
        $data['show'] = 1;
        $module['data'] = $data;
        return $module;
    }


    public function coupon($settlementGoodsAmountList, $userCouponId): array
    {
        $module = [];
        $module['type'] = 'coupon';
        $data = [];
        $couponList = UserCouponService::getInstance()->getUsableList($settlementGoodsAmountList);
        $couponTotal = count($couponList);
        $data['show'] = 1;
        $data['form'] = ['user_coupon_id' => $userCouponId];
        $data['list'] = $couponList;
        $data['field'] = [];
        $data['field']['text'] = $couponTotal ? $couponTotal . '张可用' : '无可用优惠券';
        $data['field']['discount_amount'] = 0;
        foreach ($couponList as $coupon) {
            if ($coupon['user_coupon_id'] != $userCouponId) continue;
            $data['field']['text'] =
                '满' . $coupon['minimum'] . '减' . $coupon['discount'];
            $data['field']['discount_amount'] = $coupon['discount'];
        }
        $module['data'] = $data;
        return $module;
    }

}