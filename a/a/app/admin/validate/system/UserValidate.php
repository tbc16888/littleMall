<?php
declare(strict_types=1);

namespace app\admin\validate\system;

use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'account' => 'require|max:25',
    ];

    protected $message = [
        'account.require' => '账号为空',
        'account.max' => '账号最多不能超过25个字符',
    ];
}