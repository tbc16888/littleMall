<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;

class GoodsIntegralService extends BaseService
{

    const INTEGRAL_DISABLE = 1; // 不允许使用
    const INTEGRAL_AND_CASH = 2;// 积分加现金
    const INTEGRAL_OR_CASH = 3; // 积分或现金
    const INTEGRAL_ONLY = 4;    // 只允许积分

    // 获取商品中各种积分的使用统计
    public function getGoodsListIntegralInfo(array $goodsList): array
    {
        $statistics = [];
        $statistics[self::INTEGRAL_DISABLE] = 0;
        $statistics[self::INTEGRAL_AND_CASH] = 0;
        $statistics[self::INTEGRAL_OR_CASH] = 0;
        $statistics[self::INTEGRAL_ONLY] = 0;
        foreach ($goodsList as $goods) {
            $statistics[$goods['use_integral_type']] +=
                $goods['use_integral'] * $goods['goods_number'];
        }
        return $statistics;
    }
}