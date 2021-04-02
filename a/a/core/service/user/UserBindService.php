<?php
declare(strict_types=1);

namespace core\service\user;

use core\base\BaseService;
use core\base\Singleton;
use think\exception\ValidateException;

class UserBindService extends BaseService
{
    const QQ = 1;
    const WX = 2;
    const WX_MINI_PROGRAM = 3;

    protected string $table = 'user_bind';
    protected string $tableUniqueKey = 'bind_id';

    // 绑定
    public function bind($userId, int $platform, string $openId, array $form = []): self
    {
        $form['user_id'] = $userId;
        $form['open_id'] = $openId;
        $form['platform'] = $platform;
        $form['bind_time'] = date('Y-m-d H:i:s');
        $this->setFormData($form)->addOrUpdate();
        return $this;
    }

    // 信息
    public function info(string $openId, int $platform)
    {
        return $this->dbQuery()->where('open_id', $openId)
            ->where('platform', $platform)->find();
    }

}