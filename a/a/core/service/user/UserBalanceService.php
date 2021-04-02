<?php
declare(strict_types=1);

namespace core\service\user;

use think\facade\Db;
use core\service\user\UserAssetBaseService;
use core\service\user\UserService;

class UserBalanceService extends UserAssetBaseService
{
    protected string $table = 'user_balance';

    // 当前
    public function current($userId): string
    {
        $condition = [];
        $condition['user_id'] = $userId;
        $info = UserService::getInstance()->dbQuery()->where($condition)->find();
        $balance = null === $info ? 0 : $info['balance'];
        return strval($balance);
    }

    public function change($userId, float $amount, int $type, string $desc = ''): self
    {
        $condition = [];
        $condition['user_id'] = $userId;
        if ($amount < 0) $condition['balance'] = Db::raw('>= ' . abs($amount));
        UserService::getInstance()->dbQuery()->where($condition)->limit(1)
            ->inc('balance', $amount)->limit(1)->update();
        $this->addRecord($userId, $amount, $type, $desc);
        return $this;
    }
}