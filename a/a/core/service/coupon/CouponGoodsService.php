<?php
declare(strict_types=1);

namespace core\service\coupon;

use core\base\BaseService;
use spec\League\Flysystem\Cached\CachedAdapterSpec;
use think\exception\ValidateException;

class CouponGoodsService extends BaseService
{

    protected string $table = 'coupon_goods';
    protected string $tableUniqueKey = 'coupon_goods_id';

    public function getList(array $params = [], int $page = 1, int $size = 10)
    {
        $field = 'cg.*, g.goods_name, g.goods_image, g.shop_price';
        $instance = $this->db()->where($params)->field($field)
            ->join('goods g', 'cg.goods_id = g.goods_id');
        if ($page == 1 && $size == 1) return $instance->find();
        return $instance->page($page, $size)->select()->toArray();
    }

    public function setGoods($couponId, array $goodsList = []): self
    {
        $condition = [];
        $condition['coupon_id'] = $couponId;
        $this->db()->where($condition)->delete();

        $gData = [];
        foreach ($goodsList as $key => $goodsId) {
            $gData[] = [
                'coupon_goods_id' => static::buildSnowflakeId($key),
                'coupon_id' => $couponId,
                'goods_id' => $goodsId
            ];
        }
        if (count($gData) && !$this->db()->insertAll($gData)) {
            throw new BusinessException('操作失败');
        }
        return $this;
    }


    public function getCouponGoods($couponId): array
    {
        $condition = [];
        $condition['coupon_id'] = $couponId;
        return $this->db()->where($condition)->select()->toArray();
    }
}