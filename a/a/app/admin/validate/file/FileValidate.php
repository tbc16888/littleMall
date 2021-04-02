<?php
declare(strict_types=1);

namespace app\admin\validate\file;

use think\Validate;

class FileValidate extends Validate
{
    protected $rule = [
        'file_title' => 'require|max:200',
        'file_url' => 'require|url',
    ];

    protected $message = [
        'file_title.require' => '文件名为空',
        'file_title.max' => '文件名最多不能超过400个字符',
        'file_url.require' => '文件名为空',
    ];
}