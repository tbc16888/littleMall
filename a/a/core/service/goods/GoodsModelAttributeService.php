<?php
declare(strict_types=1);

namespace core\service\goods;

use _HumbugBox5f65e914a905\Nette\Schema\ValidationException;
use core\base\BaseService;
use think\exception\ValidateException;

class GoodsModelAttributeService extends BaseService
{
    protected string $table = 'goods_model_attribute';
    protected string $tableUniqueKey = 'attr_id';

    public function setAttribute($modelId, array $attribute = []): bool
    {
        $eData = [];
        $gData = $rData = [];
        $updateList = [];

        foreach ($attribute as $key => $item) {

            // 检测属性名重复
            if (!$item['attr_name']) throw new ValidationException('属性名称为空', 1);
            if (isset($eData[$item['attr_name']])) throw new ValidationException('属性名称相同', 1);

            $rData['model_id'] = $modelId;
            $rData['attr_type'] = $item['attr_type'];
            $rData['attr_name'] = $item['attr_name'];
            $rData['options'] = $item['options'];
            $rData['input_type'] = $item['input_type'];
            $rData['unit'] = $item['unit'] ?? '';
            $rData['status'] = intval($item['status']);
            $rData['sort_weight'] = $item['sort_weight'];
            $rData['is_delete'] = 0;
            if (isset($item['attr_id']) && $item['attr_id']) {
                $updateList[] = $this->db()->where('attr_id', $item['attr_id'])
                    ->fetchSql(true)->limit(1)->update($rData);
            } else {
                $rData['create_time'] = date('Y-m-d H:i:s');
                $rData['attr_id'] = static::buildSnowflakeId($key);
                $gData[] = $rData;
            }
            $eData[$item['attr_name']] = 1;
        }
        $this->db()->where('model_id', $modelId)->update(['is_delete' => 1]);
        foreach ($updateList as $sql) \think\facade\Db::execute($sql);
        if (count($gData)) $this->db()->insertAll($gData);
        return true;
    }
}