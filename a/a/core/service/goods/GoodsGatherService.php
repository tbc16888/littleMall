<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;

class GoodsGatherService extends BaseService
{

    protected string $table = 'goods_crawl';
    protected string $tableUniqueKey = 'gather_id';

}