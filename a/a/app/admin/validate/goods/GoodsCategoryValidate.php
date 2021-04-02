<?php
declare(strict_types=1);

namespace app\admin\validate\goods;

use think\Validate;

class GoodsCategoryValidate extends Validate
{
    protected $rule = [
        'cat_name' => 'require|max:60',
    ];

    protected $message = [
        'cat_name.require' => '名称为空',
        'cat_name.max' => '名称最多不能超过60个字符'
    ];
}