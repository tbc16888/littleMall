<?php
declare(strict_types=1);

namespace app\v1\validate;

use think\Validate;

class MobileCodeValidate extends Validate
{
    protected $rule = [
        'mobile' => 'require|mobile',
        'action' => 'require'
    ];

    protected $message = [
        'mobile.require' => '手机号为空',
        'mobile.mobile' => '手机号格式不正确',
        'action.require' => '数据错误',
    ];
}