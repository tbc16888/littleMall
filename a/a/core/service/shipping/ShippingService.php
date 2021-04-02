<?php
declare(strict_types=1);

namespace core\service\shipping;

use core\base\BaseService;

class ShippingService extends BaseService
{

    protected string $table = 'shipping';
    protected string $tableUniqueKey = 'shipping_id';

}