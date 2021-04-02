<?php
declare(strict_types=1);

namespace app\admin\validate\goods;

use think\Validate;

class GoodsBrandValidate extends Validate
{
    protected $rule = [
        'brand_name' => 'require|max:60',
        'brand_logo' => 'require'
    ];

    protected $message = [
        'brand_name.require' => '品牌名称为空',
        'brand_name.max' => '名称最多不能超过60个字符',
        'brand_logo.require' => '品牌LOGO为空',
    ];
}