<?php
declare (strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsCategoryService;
use core\service\goods\GoodsCategoryMobileService;

class CategoryController extends BaseController
{

    /**
     * 列表
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        if (!($parentId = $request->get('parent_id', ''))) $parentId = '';

        $condition = [];
        $condition['status'] = 1;
        $condition['is_delete'] = 0;
        $field = 'cat_name as label, cat_id as value, icon';
        $catList = GoodsCategoryMobileService::getInstance()
            ->getChildrenList($parentId, $condition);
        foreach ($catList as $key => &$cat) {
            $cat['children'] = [];
            if ($key > 0 && !$parentId) continue;
            $cat['children'] = GoodsCategoryMobileService::getInstance()
                ->getChildrenList($cat['cat_id'], $condition, true);
        }
        return finish(0, '获取成功', ['list' => $catList]);
    }
}
