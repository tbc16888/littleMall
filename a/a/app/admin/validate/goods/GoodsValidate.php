<?php
declare(strict_types=1);

namespace app\admin\validate\goods;

use think\Validate;

class GoodsValidate extends Validate
{
    protected $rule = [
        'cat_id' => 'require',
        'goods_name' => 'require|max:60',
        'goods_image' => 'require'
    ];

    protected $message = [
        'cat_id.require' => '商品主分类为空',
        'goods_name.require' => '商品名称为空',
        'goods_name.max' => '商品名称最多不能超过60个字符',
        'goods_image.require' => '请上传至少一张商品主图',
    ];
}