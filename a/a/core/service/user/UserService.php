<?php
declare(strict_types=1);

namespace core\service\user;

use core\base\BaseService;
use think\exception\ValidateException;

class UserService extends BaseService
{

    protected string $table = 'user';
    protected string $tableUniqueKey = 'user_id';

    // 密码加密
    public static function password(string $password): string
    {
        return sha1($password);
    }

    protected function onBeforeInsert()
    {
        if (!($this->formData['account'] ?? '')) {
            $this->formData['account'] = static::buildSnowflakeId();
        }
        if (!($this->formData['password'] ?? '')) {
            throw new ValidateException('密码为空');
        }
        if (($mobile = $this->formData['mobile'] ?? '')) {
            if (!\tbc\utils\Validate::isChinaMobile($mobile)) {
                throw new ValidateException('手机号格式不正确');
            }
        }
        if (!($this->formData['nick_name'] ?? '')) {
            $this->formData['nick_name'] = '用户'.mt_rand(1000, 9999);
        }
        if (!($this->formData['avatar'] ?? '')) {
            $this->formData['avatar'] = 'http://qiniu.tongbaochun.com/Frelv7lnqTQ6FZo7HMcbr-wuPdlx';
        }
    }

    protected function onBeforeExecute()
    {
        if (($mobile = $this->formData['mobile'] ?? '')) {
            $info = $this->dbQuery()->where('mobile', $mobile)->find();
            if (null !== $info && $this->tableUniqueId != $info['user_id']) {
                throw new ValidateException('手机号已存在');
            }
        }
        if (isset($this->formData['password'])) {
            if ($this->formData['password']) {
                $this->formData['password'] =
                    static::password($this->formData['password']);
            } else {
                // throw new ValidateException('密码为空');
                unset($this->formData['password']);
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


    public function info($userId = '', $mobile = ''): ?array
    {
        $condition = [];
        if ($userId) $condition['user_id'] = $userId;
        if ($mobile) $condition['mobile'] = $mobile;
        return $this->db()->where($condition)->where('is_delete', 0)->find();
    }
}