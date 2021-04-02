<?php
declare(strict_types=1);

namespace app\admin\validate\user;

use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'account' => 'require|max:40|alphaDash',
        'password' => 'require',
        'mobile' => 'mobile',
        'email' => 'email'
    ];

    protected $message = [
        'account.require' => '账号为空',
        'account.alphaDash' => '账号格式错误「只允许字母和数字、下划线_」',
        'account.max' => '账号最多不能超过40个字符',
        'password.require' => '密码为空',
        'mobile' => '手机号格式错误',
        'email' => '邮箱格式错误',
    ];

    // 编辑验证
    public function sceneEdit()
    {
        $this->remove('password', 'require');
    }
}