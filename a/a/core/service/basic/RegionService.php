<?php
declare(strict_types=1);

namespace core\service\basic;

use core\base\BaseService;

class RegionService extends BaseService
{
    protected string $table = 'region';

    public function info($regionCode)
    {
        return $this->db()->where('region_code', $regionCode)
            ->find();
    }
}