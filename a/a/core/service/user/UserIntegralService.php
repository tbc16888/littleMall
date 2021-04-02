<?php
declare(strict_types=1);

namespace core\service\user;

use think\facade\Db;
use core\service\user\UserAssetBaseService;
use core\service\user\UserService;

class UserIntegralService extends UserAssetBaseService
{
    protected string $table = 'user_integral';

    public function current($userId): int
    {
        $condition = [];
        $condition['user_id'] = $userId;
        $info = UserService::getInstance()->dbQuery()->where($condition)->find();
        return null === $info ? 0 : $info['integral'];
    }

    public function change($userId, int $amount, int $type, string $desc = ''): self
    {
        $condition = [];
        $condition['user_id'] = $userId;
        if ($amount < 0) $condition['integral'] = Db::raw('>= ' . abs($amount));
        UserService::getInstance()->dbQuery()->where($condition)->limit(1)
            ->inc('integral', $amount)->update();
        $this->addRecord($userId, $amount, $type, $desc);
        return $this;
    }
}