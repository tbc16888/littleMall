<?php
declare(strict_types=1);

namespace core\service\system;

use core\base\BaseService;

class MenuService extends BaseService
{

    protected string $table = 'system_menu';
    protected string $tableUniqueKey = 'menu_id';


    public function import()
    {
        $this->db()->where('id', '>', 0)->delete();
        $treeList = [
            \core\service\system\menu\Basic::Make(),
            \core\service\system\menu\Auth::Make(),
            \core\service\system\menu\SystemConfig::Make(),
            \core\service\system\menu\User::Make(),
            \core\service\system\menu\UserAccount::Make(),
            \core\service\system\menu\Goods::Make(),
            \core\service\system\menu\Order::Make(),
            \core\service\system\menu\App::Make(),
            \core\service\system\menu\Promotion::Make(),
            \core\service\system\menu\File::Make(),
            \core\service\system\menu\Journal::Make(),
            \core\service\system\menu\Statistics::Make(),
        ];

        $dispute = array_flip(['controller', 'action', 'module', 'menu_name', 'menu_code', 'parent_id', 'full_menu_name']);

        foreach ($treeList as $one) {
            $one['menu_code'] = implode(':', [$one['controller']]);
            $oneInfo = $this->info($one['menu_code']);
            if (null === $oneInfo) {
                $oneInfo = $this->setFormData(array_intersect_key($one, $dispute))
                    ->addOrUpdate()->getFormData();
            }
            $one['menu_id'] = $oneInfo['menu_id'];

            foreach ($one['children'] as $two) {
                $two = array_merge($one, $two);
                $two['menu_code'] = implode(':', [$one['module'], $two['controller']]);
                $two['parent_id'] = $one['menu_id'];
                $two['full_menu_name'] = implode('/', array_filter([$one['menu_name'], $two['menu_name']]));
                $twoInfo = $this->info($two['menu_code']);
                if (null === $twoInfo) $twoInfo = $this->setFormData(array_intersect_key($two, $dispute))
                    ->addOrUpdate()
                    ->getFormData();
                $two['menu_id'] = $twoInfo['menu_id'];

                $two['children'] = $two['children'] ?? [];
                if (!count($two['children'])) continue;
                foreach ($two['children'] as $three) {
                    $three = array_merge($two, $three);
                    $three['menu_code'] = implode(':', [$one['module'], $two['controller'], $three['action']]);
                    $three['parent_id'] = $two['menu_id'];
                    $three['full_menu_name'] = implode('/', array_filter([$one['menu_name'], $two['menu_name'],
                        $three['menu_name']]));
                    $this->setFormData(array_intersect_key($three, $dispute))->addOrUpdate();
                }
            }
        }
    }


    public function info(string $menuCode)
    {
        $condition = [];
        $condition['menu_code'] = $menuCode;
        return $this->db()->where($condition)->find();
    }


    public function getTree(string $parentId = ''): array
    {
        $condition = [];
        $condition['parent_id'] = $parentId;
        $menuList = $this->dbQuery($condition)->select()->toArray();
        foreach ($menuList as &$menu) {
            $menu['children'] = $this->getTree($menu['menu_id']);
            $menu['children_count'] = count($menu['children']);
        }
        return $menuList;
    }
}