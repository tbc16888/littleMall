<?php
declare(strict_types=1);

namespace core\service\system;

use core\base\BaseService;
use think\exception\ValidateException;

class UserService extends BaseService
{

    protected string $table = 'system_user';
    protected string $tableUniqueKey = 'user_id';

    // 密码加密
    public static function password(string $password): string
    {
        return sha1($password);
    }

    // 创建超级用户
    public static function createSuperUser()
    {
        (new self())->setFormData(['account' => 'admin', 'password' => 'admin',
            'nick_name' => '超级管理员'])->addOrUpdate();
    }

    protected function onBeforeExecute()
    {
        if (isset($this->formData['account'])) {
            if ($this->isExistsAccount($this->formData['account'])) {
                throw new ValidateException('账号已存在');
            }
        }
        if (isset($this->formData['password'])) {
            if ($this->formData['password']) {
                $this->formData['password'] =
                    static::password($this->formData['password']);
            }else{
                throw new ValidateException('密码为空');
            }
        }
    }

    // 判断用户名是否存在
    public function isExistsAccount(string $account): bool
    {
        $info = $this->dbQuery()->where('account', $account)->find();
        if (null !== $info && $info['user_id'] !== $this->tableUniqueId) return true;
        return false;
    }
}