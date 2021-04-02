<?php
declare(strict_types=1);

namespace core\service\user;

use core\base\BaseService;
use core\exception\BusinessException;

class UserAssetBaseService extends BaseService
{
    protected string $tableUniqueKey = 'log_id';

    const CHANGE = 10100;
    const CONSUME = 10200;
    const CONSUME_REFUND = 10201;

    public static $typeMapper = [
        self::CHANGE => '手动调整',
        self::CONSUME => '消费',
        self::CONSUME_REFUND => '退款',
    ];

    public function addRecord($userId, $amount, int $type, string $desc = ''): self
    {
        $rData = [];
        $rData['user_id'] = $userId;
        $rData['change_amount'] = $amount;
        $rData['change_type'] = $type;
        $rData['change_desc'] = $desc;
        $rData['change_time'] = date('Y-m-d H:i:s');
        $rData['create_time'] = $rData['change_time'];
        $rData['update_time'] = $rData['change_time'];
        $this->setFormData($rData)->addOrUpdate();
        return $this;
    }
}