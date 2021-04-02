<?php
declare(strict_types=1);

namespace core\service\category;

use core\base\BaseService;
use think\exception\ValidateException;

class CategoryService extends BaseService
{
    protected string $table = 'category';
    protected string $tableUniqueKey = 'cat_id';

    protected function onBeforeExecute()
    {
        $info = $this->dbQuery()->where('cat_name', $this->formData['cat_name'])
            ->find();
        if (null !== $info && $this->tableUniqueId != $info['cat_id']) {
            throw new ValidateException('名称已存在');
        }
        $this->updateChildren($info['parent_id']);
    }

    public function updateChildren($catId)
    {
        $count = $this->dbQuery()->where('parent_id', $catId)->count();
        $this->dbQuery()->where('cat_id', $catId)->limit(1)
            ->update(['children' => $count]);
    }
}