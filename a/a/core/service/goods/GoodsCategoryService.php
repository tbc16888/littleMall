<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;
use think\exception\ValidateException;
use core\exception\BusinessException;

class GoodsCategoryService extends BaseService
{
    protected string $table = 'goods_category';
    protected string $tableUniqueKey = 'cat_id';

    protected function onBeforeExecute()
    {
        $this->formData['parent_id'] = $this->formData['parent_id'] ?? '';
        $info = $this->dbQuery()->where('cat_name', $this->formData['cat_name'])
            ->where('parent_id', $this->formData['parent_id'])->find();
        if (null !== $info && $this->tableUniqueId != $info['cat_id']) {
            throw new ValidateException('名称已存在');
        }
    }

    // 获取所有的子类
    public function getAllChildren($catId): array
    {
        $condition = [];
        $condition['parent_id'] = $catId;
        $fields = 'cat_id, cat_name, parent_id';
        $catList = $this->dbQuery()->where($condition)->select()->toArray();
        foreach ($catList as $item) {
            $catList = array_merge($catList, $this->getAllChildren($item['cat_id']));
        }
        return $catList;
    }

    public function getChildrenList($parentId, $params = [], bool $recursion = false): array
    {
        $condition = [];
        $condition['parent_id'] = $parentId;
        $condition = array_merge($condition, $params);
        $field = 'cat_name, cat_id, icon';
        $catList = $this->dbQuery($condition)->field($field)
            ->where('parent_id', $parentId)->select()->toArray();
        foreach ($catList as &$item) {
            if ($recursion) {
                $item['children'] = $this->getChildrenList($item['cat_id'],
                    $params, $recursion);
            } else {
                $item['children'] = [];
            }
        }
        return $catList;
    }


    public function getPath($catId, $field = ''): array
    {
        $catPath = [];
        $catInfo = $this->dbQuery()->where('cat_id', $catId)->find();
        if (null === $catInfo) return [];
        if ($catInfo['parent_id']) {
            $catPath = array_merge($catPath, $this->getPath($catInfo['parent_id']));
        }
        $catPath[] = $catInfo;
        if (!$field) return $catPath;
        $fieldPath = [];
        foreach ($catPath as $item) $fieldPath[] = $item[$field];
        return $fieldPath;
    }


    // 设置分类模型
    public function setModel($catId, $modelId, $isSyncChildren): self
    {
        $catList = [['cat_id' => $catId]];
        if ($isSyncChildren) {
            $catList = array_merge($catList, $this->getAllChildren($catId));
        }
        $catIdList = [];
        foreach ($catList as $cat) $catIdList[] = $cat['cat_id'];
        if (!$this->db()->whereIn('cat_id', implode(',', $catIdList))
            ->update(['model_id' => $modelId])) {
            throw new BusinessException('操作失败');
        }
        return $this;
    }

    public function updateChildrenTotal($catId)
    {
        $total = $this->dbQuery()->where('parent_id', $catId)->count();
        $this->dbQuery()->where('cat_id', $catId)->update([
            'children' => $total
        ]);
    }
}