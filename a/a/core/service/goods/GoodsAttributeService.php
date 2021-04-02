<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;

class GoodsAttributeService extends BaseService
{
    const BASE = 1;
    const SPEC = 2;

    protected string $table = 'goods_attribute';
    protected string $tableUniqueKey = 'attr_id';

    // 设置基本属性
    public function setBaseAttribute($goodsId, $goodsVersion, array $attrList): self
    {
        $condition = [];
        $condition['goods_version'] = $goodsVersion;
        $condition['goods_id'] = $goodsId;

        $gData = [];
        foreach ($attrList as $index => $item) {
            if (!isset($item['value'])) continue;
            if (!is_array($item['value'])) $item['value'] = [$item['value']];
            foreach ($item['value'] as $value) {
                $rData = $condition;
                $rData['attr_id'] = $item['id'];
                $rData['attr_value'] = $value;
                $rData['attr_type'] = static::BASE;
                $rData['attr_value_label'] = $value;
                $rData['goods_attr_id'] = static::buildSnowflakeId($index);
                $gData[] = $rData;
            }
        }

        // 删除旧数据
        $condition['attr_type'] = static::BASE;
        $this->db()->where($condition)->delete();
        if (count($gData) && !$this->db()->insertAll($gData)) {
            throw new ValidateException('操作失败[5002]');
        }
        return $this;
    }

    public function get($goodsId, $goodsVersion = 1, $type = 0): array
    {
        $condition = [];
        $condition['ga.goods_id'] = $goodsId;
        $condition['ga.goods_version'] = $goodsVersion;
        $condition['ga.is_delete'] = 0;
        if ($type) $condition['gta.attr_type'] = $type;
        $field = 'ga.goods_attr_id, ga.attr_id, ga.attr_value, ga.attr_value_label';
        $field .= ', ga.remark, gta.attr_name, gta.input_type, ga.image';
        $dataList = $this->db()->alias('ga')->field($field)->where($condition)
            ->join('goods_model_attribute gta', 'ga.attr_id = gta.attr_id')
            ->order('gta.sort_weight', 'desc')
            ->order('gta.id', 'asc')
            ->order('ga.id', 'asc')
            ->select()->toArray();
        $attribute = [];
        foreach ($dataList as $item) {

            if (!isset($attribute[$item['attr_id']])) {
                $initialize = [];
                $initialize['goods_attr_id'] = $item['goods_attr_id'];
                $initialize['attr_id'] = $item['attr_id'];
                $initialize['attr_name'] = $item['attr_name'];
                $initialize['value'] = [];
                $initialize['props'] = [];
                $attribute[$item['attr_id']] = $initialize;
            }
            $attribute[$item['attr_id']]['value'][] = $item['attr_value'];
            if ($item['remark']) $item['attr_value'] .= '(' . $item['remark'] . ')';
            $item['attr_value'] = trim($item['attr_value']);
            $attribute[$item['attr_id']]['props'][] = $item;
        }
        return array_values($attribute);
    }
}