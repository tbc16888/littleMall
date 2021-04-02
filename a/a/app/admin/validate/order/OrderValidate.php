<?php
declare(strict_types=1);

namespace app\admin\validate\order;

use think\Validate;

class OrderValidate extends Validate
{
    protected $rule = [
        'contact' => 'require|max:25',
        'contact_number' => 'require|mobile',
        'address' => 'require',
    ];

    protected $message = [
        'contact.require' => '联系人为空',
        'contact.max' => '联系人最多不能超过25个字符',
        'contact_number.require' => '手机号为空',
        'contact_number.mobile' => '手机号格式不正确',
        'address.require' => '详细地址为空',
    ];
}