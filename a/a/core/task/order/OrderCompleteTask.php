<?php
declare(strict_types=1);

namespace core\task\order;

use core\base\BaseTask;
use core\service\order\OrderService;
use core\constant\order\OrderStatus;

class OrderCompleteTask extends BaseTask
{
    public function handle()
    {
//        while (true) {
//            echo date('Y-m-d H:i:s') . 'OrderCompleteTask', PHP_EOL;
//            sleep(2);
//        }
    }
}