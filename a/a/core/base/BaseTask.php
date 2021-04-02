<?php
declare(strict_types=1);

namespace core\base;

use core\service\system\UserService;

class BaseTask
{
    protected string $systemUserId = '152469093268639746';

    // 获取操作员
    protected function getSystemUser(): string
    {
//        $info = UserService::getInstance()->db()
//            ->where('account', 'admin')->find();
        return $this->systemUserId;
    }
}