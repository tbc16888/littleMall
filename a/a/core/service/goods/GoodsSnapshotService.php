<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;
use think\exception\ValidateException;

class GoodsSnapshotService extends BaseService
{

    protected string $table = 'goods_snapshot';
    protected string $tableUniqueKey = 'snapshot_id';

}