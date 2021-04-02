<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;

class GoodsCollectService extends BaseService
{
    protected string $table = 'goods_collect';
    protected string $tableUniqueKey = 'collect_id';

    public function onBeforeExecute()
    {

    }

    public function isCollect($goodsId, $userId): bool
    {
        $condition = [];
        $condition['goods_id'] = $goodsId;
        $condition['user_id'] = $userId;
        $info = $this->dbQuery($condition)->find();
        if (null === $info) return false;
        return true;
    }
}