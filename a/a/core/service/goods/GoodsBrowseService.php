<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;

class GoodsBrowseService extends BaseService
{
    protected string $table = 'goods_browse';
    protected string $tableUniqueKey = 'browse_id';


    public function inc($goodsId, $userId): bool
    {
        if ($goodsId && $userId) {
            $condition = [];
            $condition['goods_id'] = $goodsId;
            $condition['user_id'] = $userId;
            $info = $this->db()->where($condition)
                ->where('create_time', '>', date('Y-m-d 00:00:00'))->find();
            $condition['browse_id'] = '';
            if (null === $info) $info = $condition;
            $info['update_time'] = date('Y-m-d H:i:s');
            $this->setFormData($info)->addOrUpdate($info['browse_id']);
        }
        return true;
    }
}