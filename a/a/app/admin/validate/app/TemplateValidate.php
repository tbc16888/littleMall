<?php
declare(strict_types=1);

namespace app\admin\validate\app;

use think\Validate;

class TemplateValidate extends Validate
{
    protected $rule = [
        'template_name' => 'require|max:25',
    ];

    protected $message = [
        'template_name.require' => '模版名称为空',
        'template_name.max' => '名称最多不能超过25个字符',
    ];
}