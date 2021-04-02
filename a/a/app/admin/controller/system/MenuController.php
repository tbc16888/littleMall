<?php
declare (strict_types=1);

namespace app\admin\controller\system;

use app\Request;
use core\base\BaseController;
use core\service\system\MenuService;

class MenuController extends BaseController
{
    /**
     * 菜单树列表
     * @RequestMapping(path="/tree", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function tree(Request $request)
    {
        $treeList = MenuService::getInstance()->getTree();
        return finish(0, '获取成功', $treeList);
    }
}

