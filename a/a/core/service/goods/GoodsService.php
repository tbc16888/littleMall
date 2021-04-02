<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;
use think\exception\ValidateException;

class GoodsService extends BaseService
{
    protected string $table = 'goods';
    protected string $tableUniqueKey = 'goods_id';

    protected function onBeforeUpdate()
    {
        $info = $this->db()->where('goods_id', $this->tableUniqueId)
            ->where('is_delete', 0)->find();
        $this->formData['goods_version'] = $info['goods_version'];

        // 当前版本有订单增加版本号
        /*$needCreateSnapshot = false;
        $condition = [];
        $condition['goods_id'] = $this->tableUniqueId;
        $condition['goods_version'] = $info['goods_version'];
        if (null !== $this->db('order_goods')->where($condition)->find()) {
            $needCreateSnapshot = true;
        }
        if ($needCreateSnapshot) $this->formData['goods_version'] += 1;*/
    }
}