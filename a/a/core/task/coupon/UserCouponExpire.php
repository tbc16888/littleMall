<?php
declare(strict_types=1);

namespace core\task\coupon;

use core\base\BaseTask;
use core\service\coupon\UserCouponService;

class UserCouponExpire extends BaseTask
{
    public function handle()
    {
        while (true) {
            UserCouponService::getInstance()->db()
                ->where('status', '=', UserCouponService::NOT_USED)
                ->where('effective_end_time', '<', date('Y-m-d H:i:s'))
                ->chunk(100, function ($dataList) {
                    foreach ($dataList as $data) {
                        UserCouponService::getInstance()->db()
                            ->where('id', $data['id'])
                            ->limit(1)->update([
                                'status' => UserCouponService::EXPIRED
                            ]);
                    }
                });
            sleep(5);
        }
    }
}