<?php
declare(strict_types=1);

namespace app\admin\validate\app;

use think\Validate;

class VersionValidate extends Validate
{
    protected $rule = [
        'platform' => 'require',
        'version_name' => 'require|max:25',
    ];

    protected $message = [
        'platform.require' => '请选择平台',
        'version_name.require' => '版本名称为空',
        'version_name.max' => '名称最多不能超过25个字符',
    ];
}