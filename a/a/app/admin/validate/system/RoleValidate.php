<?php
declare(strict_types=1);

namespace app\admin\validate\system;

use think\Validate;

class RoleValidate extends Validate
{
    protected $rule = [
        'role_name' => 'require|max:25'
    ];

    protected $message = [
        'role_name.require' => '角色名称为空',
        'role_name.max' => '角色名称不能大于25个字符'
    ];

}