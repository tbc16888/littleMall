<?php
declare(strict_types=1);

namespace app\v1\validate;

use think\Validate;

class UserAddressValidate extends Validate
{
    protected $rule = [
        'contact' => 'require|max:30',
        'contact_number' => 'require|mobile',
        'address' => 'require',
        'house_number' => 'require'
    ];

    protected $message = [
        'contact.require' => '联系人姓名为空',
        'contact_number.require' => '联系电话为空',
        'contact_number.mobile' => '电话格式不正确',
        'address.require' => '详细地址为空',
        'house_number.require' => '门牌号为空',
    ];
}