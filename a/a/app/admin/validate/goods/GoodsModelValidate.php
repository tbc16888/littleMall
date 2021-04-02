<?php
declare(strict_types=1);

namespace app\admin\validate\goods;

use think\Validate;

class GoodsModelValidate extends Validate
{
    protected $rule = [
        'model_name' => 'require|max:60',
    ];

    protected $message = [
        'model_name.require' => '模型名称为空',
        'model_name.max' => '名称最多不能超过60个字符',
    ];
}