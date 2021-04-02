<?php
declare(strict_types=1);

namespace core\constant\goods;

class GoodsIntegral
{
    const INTEGRAL_DISABLE = 1; // 不允许使用
    const INTEGRAL_AND_CASH = 2; // 积分 + 现金
    const INTEGRAL_OR_CASH = 3; // 积分或现金
    const INTEGRAL_ONLY = 4; // 只允许积分
}