<?php
declare(strict_types=1);

namespace app\v1\validate;

use think\Validate;

class CartValidate extends Validate
{
    protected $rule = [
        'goods_id' => 'require',
    ];

    protected $message = [
        'goods_id.require' => '商品ID为空',
    ];
}