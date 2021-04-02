<?php
declare(strict_types=1);

namespace core\task\order;

use core\base\BaseTask;
use core\service\order\OrderService;
use core\constant\order\OrderStatus;

class OrderCancelTask extends BaseTask
{
    public function handle()
    {
        while (true) {
            OrderService::getInstance()->db()
                ->where('order_status', '<', OrderStatus::CANCEL)
                ->where('pay_deadline_time', '<', date('Y-m-d H:i:s'))
                ->chunk(100, function ($orderList) {
                    foreach ($orderList as $order) {
                        OrderService::getInstance()->setAdminId($this->getSystemUser())
                            ->cancel($order['order_sn']);
                    }
                });
            sleep(5);
        }
    }
}