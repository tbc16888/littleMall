<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;
use think\exception\ValidateException;

class GoodsModelService extends BaseService
{
    protected string $table = 'goods_model';
    protected string $tableUniqueKey = 'model_id';

    protected function onBeforeExecute()
    {
        $info = $this->dbQuery()->where('model_name', $this->formData['model_name'])
            ->find();
        if (null !== $info && $this->tableUniqueId != $info['model_id']) {
            throw new ValidateException('模型名称已存在');
        }
    }
}