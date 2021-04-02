<?php
declare(strict_types=1);

namespace app\v1\validate;

use think\Validate;

class GoodsCommentValidate extends Validate
{
    protected $rule = [
        'text' => 'require|max:255',
    ];

    protected $message = [
        'text.require' => '请输入评论',
        'text.max' => '评论不能大于255个字符',
    ];
}