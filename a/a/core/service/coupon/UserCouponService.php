<?php
declare(strict_types=1);

namespace core\service\coupon;

use core\exception\BusinessException;
use think\facade\Db;
use core\base\BaseService;
use think\exception\ValidateException;
use core\service\coupon\CouponService;

class UserCouponService extends BaseService
{
    const NOT_USED = 1;
    const USED = 2;
    const EXPIRED = 3;

    protected string $table = 'coupon_user';
    protected string $tableUniqueKey = 'user_coupon_id';


    protected function onBeforeInsert()
    {
        if (!($this->formData['user_id'] ?? '')) {
            throw new ValidateException('用户ID错误');
        }
    }

    // 是否
    public function isReceived($couponId, $userId): int
    {
        if (!$couponId || !$userId) return 0;
        $info = $this->db()->where('coupon_id', $couponId)
            ->where('user_id', $userId)
            ->where('status', static::NOT_USED)->find();
        return null === $info ? 0 : 1;
    }

    // 改变状态
    public function changeStatus($userCouponId, $status, $orderSn = ''): self
    {
        $condition = [];
        $info = $this->db()->where('user_coupon_id', $userCouponId)
            ->where('is_delete', 0)->find();
        $rData = [];
        $rData['status'] = $status;
        if ($orderSn) $rData['order_sn'] = $orderSn;
        if ($status == static::EXPIRED) $condition['status'] = static::NOT_USED;
        if ($status == static::USED) $condition['status'] = static::NOT_USED;
        if ($status == static::NOT_USED) $condition['status'] = static::USED;
        $result = $this->db()->where('id', $info['id'])->limit(1)
            ->where($condition)->update($rData);
        if (!$result) throw new BusinessException('操作失败');
        return $this;
    }


    // 获取列表
    public function getList(array $condition = [], int $page = 1, int $size = 10)
    {
        $field = 'c.coupon_name, c.discount, c.minimum, c.coupon_desc, c.use_scope';
        $field .= ', uc.effective_start_time, uc.effective_end_time, uc.status';
        $field .= ', uc.order_sn, uc.create_time, uc.use_time, uc.coupon_id';
        $field .= ', uc.user_coupon_id';
        $instance = $this->db()->alias('uc')->field($field)->where($condition)
            ->join('coupon c', 'uc.coupon_id = c.coupon_id');
        if ($page == 1 && $size == 1) return $instance->find();
        return $instance->page($page, $size)->select()->toArray();
    }


    // 获取可用列表
    public function getUsableList(array $goodsAmountList, string $datetime = '')
    {
        if (!$datetime) $datetime = date('Y-m-d H:i:s');

        $baseCondition = [];
        $baseCondition['uc.user_id'] = $this->userId;
        $baseCondition['uc.status'] = 1;
        $baseCondition['uc.effective_start_time'] = Db::raw("< '$datetime'");
        $baseCondition['uc.effective_end_time'] = Db::raw("> '$datetime'");

        // 全部可用
        $condition = $baseCondition;
        $condition['c.minimum'] = Db::raw("< " . array_sum($goodsAmountList));
        $condition['c.use_scope'] = CouponService::SCOPE_ALL;
        $couponList = $this->getList($condition, 1, 1000);

        // 部分可用
        $condition = $baseCondition;
        $condition['c.use_scope'] = CouponService::SCOPE_PARTIALLY_AVAILABLE;
        $field = 'c.coupon_name, c.discount, c.minimum, c.coupon_desc, c.use_scope';
        $field .= ', uc.*, cg.goods_id';
        $partInstance = $this->db()->alias('uc')->field($field)->where($condition)
            ->join('coupon c', 'uc.coupon_id = c.coupon_id')
            ->join('coupon_goods cg', 'c.coupon_id = cg.coupon_id');
        \think\facade\Log::debug($partInstance->fetchSql(true)->select());
        $designated = $partInstance->select()->toArray();
        $recombination = [];
        foreach ($designated as $couponGoods) {
            $couponId = $couponGoods['coupon_id'];
            if (!isset($recombination[$couponId])) {
                $recombination[$couponId] = $couponGoods;
                $recombination[$couponId]['goods'] = [];
            }
            $recombination[$couponId]['goods'][] = $couponGoods['goods_id'];
        }

        foreach ($recombination as $couponInfo) {
            $sum = 0;
            foreach ($couponInfo['goods'] as $goodsId) {
                if (!isset($goodsAmountList[$goodsId])) continue;
                $sum += $goodsAmountList[$goodsId];
            }
            if ($sum >= $couponInfo['minimum']) array_push($couponList, $couponInfo);
        }

        // 部分不可用
        $condition['c.use_scope'] = CouponService::SCOPE_PARTIALLY_NOT_AVAILABLE;
        $designated = $partInstance->where($condition)->select()->toArray();
        $recombination = [];
        foreach ($designated as $couponGoods) {
            if (!isset($recombination[$couponGoods['user_coupon_id']])) {
                $recombination[$couponGoods['user_coupon_id']] = $couponGoods;
                $recombination[$couponGoods['user_coupon_id']]['goods'] = [];
            }
            $recombination[$couponGoods['user_coupon_id']]['goods'][] = $couponGoods['goods_id'];
        }

        foreach ($recombination as $couponInfo) {
            // 去除排除中的商品，在对比剩余商品的总价是否达到
            $differenceSet = array_diff_key($goodsAmountList,
                array_flip($couponInfo['goods']));
            if (array_sum($differenceSet) >= $couponInfo['minimum']) {
                array_push($couponList, $couponInfo);
            }
        }
        return $couponList;
    }


    // 获取不可用商品
    public function getUnUsableList(array $goodsList)
    {
        $goodsAmount = Cart::getInstance()->getGoodsTotalPriceOfEach($goodsList);
        $totalGoodsAmount = array_sum($goodsAmount);

        $resultList = [];

        // 获取还未使用的优惠券
        $datetime = date('Y-m-d H:i:s');
        $condition = [];
        $condition['uc.user_id'] = $this->userId;
        $condition['uc.status'] = static::NOT_USED;
        $field = 'c.coupon_id, c.coupon_name, c.coupon_desc, c.minimum, c.discount, c.use_scope';
        $field .= ', uc.status, uc.effective_start_time, uc.effective_end_time, uc.id as user_coupon_id';
        $couponList = Db::name('user_coupon uc')->where($condition)->field($field)
            ->join('coupon c', 'uc.coupon_id = c.coupon_id')->select()->toArray();
        if (null === $couponList) $couponList = [];

        foreach ($couponList as $coupon) {
            if ($coupon['effective_start_time'] > $datetime || $coupon['effective_end_time'] < $datetime) {
                $coupon['unusable_reason'] = '未在有效期内';
                $resultList[] = $coupon;
                continue;
            }

            if ($coupon['use_scope'] == Coupon::SCOPE_ALL && $totalGoodsAmount < $coupon['minimum']) {
                $coupon['unusable_reason'] = '未达到指定消费金额';
                $resultList[] = $coupon;
                continue;
            }

            if ($coupon['use_scope'] > Coupon::SCOPE_ALL) {
                $couponGoodsList = CouponGoods::getInstance()->getCouponGoods($coupon['coupon_id']);
                $couponGoodsIdList = [];
                foreach ($couponGoodsList as $item) $couponGoodsIdList[$item['goods_id']] = 1;
                // 指定部分可用
                $calcSet = [];
                if ($coupon['use_scope'] == Coupon::SCOPE_PARTIALLY_AVAILABLE) {
                    $calcSet = array_intersect_key($goodsAmount, $couponGoodsIdList);
                }
                // 指定部分不可用
                if ($coupon['use_scope'] == Coupon::SCOPE_PARTIALLY_NOT_AVAILABLE) {
                    $calcSet = array_diff_key($goodsAmount, $couponGoodsIdList);
                }

                if (array_sum($calcSet) < $coupon['minimum']) {
                    $coupon['unusable_reason'] = '未达到指定消费金额';
                    $resultList[] = $coupon;
                    continue;
                }
            }
        }
        return $resultList;
    }


    // 判断优惠券是否可用
    public function isUsable(array $couponInfo, array $goodsAmountList, string $useTime = ''): bool
    {
        if (!$useTime) $useTime = date('Y-m-d H:i:s');
        if ($couponInfo['status'] != static::NOT_USED) {
            throw new BusinessException('优惠券已过期或已使用', 1);
        }
        if ($couponInfo['effective_start_time'] > $useTime) {
            throw new BusinessException('优惠券不在有效期内', 1);
        }
        if ($couponInfo['effective_end_time'] < $useTime) {
            throw new BusinessException('优惠券已过期', 1);
        }

        // 非全部商品适用
        $checkResult = true;
        if ($couponInfo['use_scope'] > CouponService::SCOPE_ALL) {
            $condition = [];
            $condition['coupon_id'] = $couponInfo['coupon_id'];
            $designated = CouponGoodsService::getInstance()->db()
                ->where($condition)->select()->toArray();
            $goodsIdList = [];
            foreach ($designated as $item) $goodsIdList[$item['goods_id']] = 1;
            $result = [];
            if ($couponInfo['use_scope'] == CouponService::SCOPE_PARTIALLY_AVAILABLE)
                $result = array_intersect_key($goodsAmountList, $goodsIdList);
            if ($couponInfo['use_scope'] == CouponService::SCOPE_PARTIALLY_NOT_AVAILABLE)
                $result = array_diff_key($goodsAmountList, $goodsIdList);
            if (array_sum($result) < $couponInfo['minimum']) $checkResult = false;
        } else {
            if (array_sum($goodsAmountList) < $couponInfo['minimum']) $checkResult = false;
        }
        if (false === $checkResult) throw new BusinessException('未达到消费金额', 1);
        return true;
    }

}