<?php
declare(strict_types=1);

namespace app\admin\validate\coupon;

use think\Validate;

class CouponValidate extends Validate
{
    protected $rule = [
        'coupon_name' => 'require|max:30',
        'coupon_total' => '>=:1',
        'discount' => '>=:0.1',
    ];

    protected $message = [
        'coupon_name.require' => '优惠券名称为空',
        'coupon_name.max:30' => '名称长度不能超过30个字符',
        'coupon_total.egt' => '发行数量必须大于0',
        'discount.egt' => '优惠金额必须大于0'
    ];
}