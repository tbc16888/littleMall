<?php
declare(strict_types=1);

namespace core\service\order;

use core\base\BaseService;
use core\exception\BusinessException;
use core\service\user\UserIntegralService;

class OrderGoodsService extends BaseService
{
    protected string $table = 'order_goods';
    protected string $tableUniqueKey = 'order_goods_id';

    /**
     * 根据订单号获取商品
     * @param $orderSn
     * @param $orderGoodsId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderGoods($orderSn, $orderGoodsId = ''): array
    {
        $condition = [];
        $condition['og.order_sn'] = $orderSn;
        if ($orderGoodsId) $condition['og.order_goods_id'] = $orderGoodsId;
        $field = 'og.goods_sku_id, og.goods_price, og.goods_number, og.integral';
        $field .= ', g.goods_name, g.goods_image, gs.goods_sku_text';
        $field .= ', og.order_goods_id, og.is_comment, og.order_sn, og.goods_amount';
        $field .= ', og.refund_status';
        return $this->db()->alias('og')->field($field)->where($condition)
            ->join('goods g', 'g.goods_id = og.goods_id and g.goods_version = og.goods_version')
            ->leftJoin('goods_sku gs', 'og.goods_sku_id = gs.goods_sku_id')
            ->select()->toArray();
    }

    // 退款
    public function refund($orderSn, $orderGoodsId = ''): self
    {
        $condition = [];
        $condition['og.order_sn'] = $orderSn;
        if ($orderGoodsId) $condition['og.order_goods_id'] = $orderGoodsId;
        $field = 'og.*, gs.goods_sku_key, o.user_id';
        $orderGoodsList = $this->db()->alias('og')->where($condition)
            ->field($field)
            ->join('order o', 'o.order_sn = og.order_sn')
            ->leftJoin('goods_sku gs', 'gs.goods_sku_id = og.goods_sku_id')
            ->select()->toArray();
        $sqlList = [];
        foreach ($orderGoodsList as $item) {

            // 释放库存
            if (!$item['is_release_stock']) {
                $condition = [];
                $condition['goods_id'] = $item['goods_id'];
                $condition['goods_sku_key'] = $item['goods_sku_key'];
                $sqlList[] = $this->db('goods_sku')->where($condition)
                    ->limit(1)->fetchSql(true)
                    ->inc('stock', $item['goods_number'])->update();

                $rData = [];
                $rData['is_release_stock'] = 1;
                $sqlList[] = $this->db()->limit(1)->fetchSql(true)
                    ->where('order_goods_id', $item['order_goods_id'])
                    ->update($rData);
            }

            // 积分返还
            if ($item['integral']) {
                UserIntegralService::getInstance()->change(
                    $item['user_id'],
                    $item['integral'],
                    UserIntegralService::CONSUME_REFUND,
                    '商品退还' . $item['order_goods_id']
                );
            }
        }

        // \think\facade\Log::sql($sqlList);
        foreach ($sqlList as $sql) {
            if (!\think\facade\Db::execute($sql)) {
                throw new BusinessException('操作失败');
            }
        }
        return $this;
    }


}