<?php
declare(strict_types=1);

namespace core\service\cart;

use core\base\BaseService;
use core\service\goods\GoodsService;
use think\exception\ValidateException;

class CartService extends BaseService
{

    protected string $table = 'cart';
    protected string $tableUniqueKey = 'cart_id';

    protected function onBeforeExecute()
    {
        $goods = GoodsService::getInstance()->dbQuery()
            ->where('goods_id', $this->formData['goods_id'])->find();
        if ($goods === null) throw new ValidateException('商品已下架或删除');
        if ($goods['sale_status'] !== 1) throw new ValidateException('商品已下架');
        if ($goods['sku_total'] > 1 && !$this->formData['goods_sku_key']) {
            throw new ValidateException('请选择商品规格');
        }
    }

    public function addOrUpdate($tableUniqueId = ''): self
    {
        $this->formData['user_id'] = $this->userId;
        if (!($this->formData['user_id'])) {
            throw new ValidateException('用户ID错误');
        }
        $info = $this->dbQuery()->where('user_id', $this->userId)
            ->where('goods_id', $this->formData['goods_id'])
            ->where('goods_sku_key', $this->formData['goods_sku_key'])->find();
        parent::addOrUpdate($info['cart_id'] ?? '');
        return $this;
    }

    // 修改选择状态
    public function changeCheckStatus(int $status, string $cartId = ''): self
    {
        $condition = [];
        $condition['user_id'] = $this->userId;
        $instance = $this->dbQuery()->where($condition);
        if ($cartId) $instance->whereIn('cart_id', $cartId);
        $instance->update(['is_checked' => $status]);
        return $this;
    }

    public function getCheckedList(array $condition = []): array
    {
        if (!$this->tableUniqueId) $condition['c.is_direct'] = 0;
        return $this->getList(array_merge($condition, ['c.is_checked' => 1]));
    }


    // 获取用户购物车
    public function getList(array $condition = []): array
    {
        $condition['c.user_id'] = $this->getUserId();
        $condition['c.is_delete'] = 0;
        $field = 'c.cart_id, c.goods_id, c.goods_sku_key, c.goods_number';
        $field .= ', c.is_checked, g.goods_name, g.goods_image, g.shop_price, g.stock';
        $field .= ', g.sale_status, g.shop_price as activity_price, g.goods_type';
        $field .= ', g.use_integral_type, g.use_integral, gs.goods_sku_text';
        $field .= ', gs.stock, gs.goods_sku_id, g.goods_version';
        $instance = $this->db()->alias('c')->field($field)->where($condition)
            ->join('goods g', 'c.goods_id = g.goods_id')
            ->leftJoin('goods_sku gs', 'gs.goods_id = c.goods_id and gs.goods_sku_key = c.goods_sku_key and gs.goods_version = g.goods_version');
        if ($this->tableUniqueId) $instance->whereIn('cart_id', $this->tableUniqueId);
        return $instance->select()->toArray();
    }
}