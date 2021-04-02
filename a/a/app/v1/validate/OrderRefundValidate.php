<?php
declare(strict_types=1);

namespace app\v1\validate;

use think\Validate;

class OrderRefundValidate extends Validate
{
    protected $rule = [
        'reason' => 'require',
    ];

    protected $message = [
        'reason.require' => '请选择退款原因'
    ];
}