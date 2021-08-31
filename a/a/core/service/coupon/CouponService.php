<?php
declare(strict_types=1);

namespace core\service\coupon;

use core\base\BaseService;
use think\exception\ValidateException;
use core\service\coupon\UserCouponService;
use core\exception\BusinessException;

class CouponService extends BaseService
{

    protected string $table = 'coupon';
    protected string $tableUniqueKey = 'coupon_id';

    const SCOPE_ALL = 1;
    const SCOPE_PARTIALLY_AVAILABLE = 2;
    const SCOPE_PARTIALLY_NOT_AVAILABLE = 3;

    public static array $distribution = [
        1 => '默认领取',
        2 => '注册赠送',
        3 => '分享赠送'
    ];

    // 领取优惠券
    public function receive()
    {

    }

    public function sendToUser($couponId, $userId): self
    {
        // 优惠券信息
        $coupon = $this->dbQuery()->where('coupon_id', $couponId)->find();
        if (null === $coupon) throw new BusinessException('优惠券不存在', 1);

        if (strtotime($coupon['release_start_time']) > time()) {
            throw new BusinessException('优惠券未到可领取时间');
        }
        if (strtotime($coupon['release_end_time']) < time()) {
            throw new BusinessException('优惠券领取时间已结束');
        }

        // 个人领取限额
        $condition = [];
        $condition['coupon_id'] = $couponId;
        $condition['user_id'] = $userId;
        $receiveCount = UserCouponService::getInstance()->db()->where($condition)
            ->count();
        if ($receiveCount > $coupon['quota']) {
            throw new BusinessException('超过个人领取限额', 1);
        }

        // 发行总数判断
        $condition = [];
        $condition['coupon_id'] = $couponId;
        $receiveCount = UserCouponService::getInstance()->db()->where($condition)
            ->count();
        if ($receiveCount > $coupon['coupon_total']) {
            throw new BusinessException('超过发行总量', 1);
        }

        $rData = [];
        $rData['user_id'] = $userId;
        $rData['coupon_id'] = $coupon['coupon_id'];
        $rData['status'] = 1;
        $rData['create_time'] = date('Y-m-d H:i:s');
        if ($coupon['effective_type'] == 1) {
            $rData['effective_start_time'] = $coupon['effective_start_time'];
            $rData['effective_end_time'] = $coupon['effective_end_time'];
        } else {
            $rData['effective_start_time'] = date('Y-m-d H:i:s');
            $rData['effective_end_time'] = date('Y-m-d H:i:s',
                time() + $coupon['effective_days'] * 86400);
        }

        // 过期不需要发放
//        if (strtotime($rData['effective_end_time']) < time()) return false;

        UserCouponService::getInstance()->setFormData($rData)->addOrUpdate();
        return $this;
    }

}