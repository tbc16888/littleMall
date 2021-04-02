<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;
use think\exception\ValidateException;
use core\service\goods\GoodsAttributeService;

class GoodsSkuService extends BaseService
{

    protected string $table = 'goods_sku';
    protected string $tableUniqueKey = 'goods_sku_id';

    public static function createItem($price, $stock = 0): array
    {
        return [
            'skuPrice' => $price,
            'skuStock' => $stock,
            'salePropKey' => '',
            'skuOuterId' => '',
            'skuBarcode' => '',
            'image' => '',
            'props' => []
        ];
    }

    public function setStockKeepingUnit($goodsId, $goodsVersion, array $skuList): self
    {
        $condition = [];
        $condition['goods_id'] = $goodsId;
        $condition['goods_version'] = $goodsVersion;
        $sqlList = [];
        $skuData = [];
        $attrData = [];
        $attribute = [];
        foreach ($skuList as $key => $item) {
            if (floatval($item['skuPrice']) <= 0) continue;
            $skuGoodsAttrId = [];
            $skuValueLabels = [];
            foreach ($item['props'] as $index => $attr) {
                $key = md5(implode(':', [$goodsId, $attr['attr_id'], $attr['value'],
                    $attr['remark']]));
                if (!isset($attribute[$key])) {
                    $rData = $condition;
                    $rData['attr_id'] = $attr['attr_id'];
                    $rData['attr_value'] = $attr['value'];
                    $rData['attr_value_label'] = $attr['label'];
                    $rData['remark'] = $attr['remark'];
                    $rData['attr_type'] = 2;
                    $goodsAttrId = $this->db('goods_attribute')->where($rData)->value('goods_attr_id');
                    $rData['image'] = $item['image'] ?? '';
                    if ($goodsAttrId) {
                        $sqlList[] = $this->db('goods_attribute')->where('goods_attr_id', $goodsAttrId)
                            ->fetchSql(true)->limit(1)
                            ->update(['is_delete' => 0]);
                        $rData['goods_attr_id'] = $goodsAttrId;
                    } else {
                        $goodsAttrId = static::buildSnowflakeId($index);
                        $rData['goods_attr_id'] = $goodsAttrId;
                        $attrData[] = $rData;
                    }
                    $attribute[$key] = $rData;
                }
                $skuGoodsAttrId[] = $attribute[$key]['goods_attr_id'];
                $skuValueLabels[] = $attr['label'];
            }

            $rData = $condition;
            $rData['goods_attr'] = implode(',', $skuGoodsAttrId);
            $rData['goods_sku_text'] = implode('', $skuValueLabels);
            $rData['shop_price'] = $item['skuPrice'];
            $rData['stock'] = $item['skuStock'];
            $rData['outer_id'] = $item['skuOuterId'] ?? '';
            $rData['bar_code'] = $item['skuBarcode'] ?? '';
            $rData['is_delete'] = 0;
            $rData['goods_sku_key'] = '';
            if ($rData['goods_attr']) $rData['goods_sku_key'] = md5($rData['goods_attr']);
            $goodsSkuId = $this->db()->where($condition)
                ->where('goods_sku_key', $rData['goods_sku_key'])
                ->value('goods_sku_id');
            $rData['goods_image'] = $item['image'] ?? '';
            if ($goodsSkuId) {
                $sqlList[] = $this->db()->where('goods_sku_id', $goodsSkuId)
                    ->fetchSql(true)->limit(1)->update($rData);
            } else {
                $rData['goods_sku_id'] = static::buildSnowflakeId();
                $skuData[] = $rData;
            }
        }

        $this->db()->where($condition)->update(['is_delete' => 1]);
        $this->db()->insertAll($skuData);
        GoodsAttributeService::getInstance()->db()->insertAll($attrData);
        foreach ($sqlList as $sql) \think\facade\Db::execute($sql);
        return $this;
    }

    public function getStockKeepingUnit($goodsId, $goodsVersion): array
    {
        $condition = [];
        $condition['ga.goods_id'] = $goodsId;
        $condition['ga.goods_version'] = $goodsVersion;
        $condition['ga.attr_type'] = GoodsAttributeService::SPEC;
        $condition['ga.is_delete'] = 0;

        // 规格属性值
        $attributeList = $attributeSort = [];
        $field = 'ga.attr_id, ga.attr_value as value, ga.attr_value_label as label, ga.remark, ga.goods_attr_id';
        $field .= ', ga.attr_value, ga.attr_value_label, ga.image';
        $attrList = GoodsAttributeService::getInstance()->db()->alias('ga')
            ->where($condition)->field($field)
            ->order('ga.id', 'desc')->select()->toArray();
        foreach ($attrList as $key => $val) {
            $attributeList[$val['goods_attr_id']] = $val;
            $attributeSort[$val['goods_attr_id']] = $key;
        }

        // 规格数据
        $skuList = [];
        $condition = [];
        $condition['goods_id'] = $goodsId;
        $condition['goods_version'] = $goodsVersion;
        $field = 'goods_sku_id, goods_sku_key, goods_attr, shop_price, stock';
        $field .= ', outer_id, bar_code, goods_image';
        $skuDbList = $this->dbQuery()->field($field)->where($condition)
            ->order('id', 'asc')
            ->select()->toArray();
        foreach ($skuDbList as $item) {
            if (!$item['goods_sku_key']) continue;
            $skuItem = [];
            $skuItem['skuPrice'] = $item['shop_price'];
            $skuItem['skuStock'] = $item['stock'];
            $skuItem['skuOuterId'] = $item['outer_id'];
            $skuItem['skuBarcode'] = $item['bar_code'];
            $skuItem['image'] = $item['goods_image'];
            $skuItem = array_merge($skuItem, $item);
            $goodsSpecAttr = explode(',', $item['goods_attr']);
            foreach ($goodsSpecAttr as $goodsAttrId) {
                $attr_data = $attributeList[$goodsAttrId];
                $skuItem['props'][] = $attr_data;
            }
            $skuList[$item['goods_sku_id']] = $skuItem;
        }
        return $skuList;
    }
}