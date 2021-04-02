<?php
declare(strict_types=1);

namespace core\service\system;

use core\base\BaseService;
use think\exception\ValidateException;

class RoleService extends BaseService
{

    protected string $table = 'system_role';
    protected string $tableUniqueKey = 'role_id';

    protected function onBeforeExecute()
    {
        $info = $this->dbQuery()->where('role_name', $this->formData['role_name'])
            ->find();
        if (null !== $info && $info['role_id'] !== $this->tableUniqueId) {
            throw new ValidateException('角色已存在');
        }
    }

    // 设置菜单
    public function setMenu(array $menuList = [], $roleId = '')
    {
        if (!$roleId) $roleId = $this->tableUniqueId;
        $gData = [];
        foreach ($menuList as $index => $menu) {
            $gData[] = [
                'role_menu_id' => static::buildSnowflakeId($index),
                'role_id' => $roleId,
                'menu_code' => $menu,
                'create_time' => date('Y-m-d H:i:s')
            ];
        }
        $this->db('system_role_menu')->where('role_id', $roleId)->delete();
        if (count($gData)) $this->db('system_role_menu')->insertAll($gData);
    }


    // 获取菜单
    public function getMenu($roleId, $field = ''): array
    {
        $condition = [];
        $condition['role_id'] = $roleId;
        $menuList = $this->db('system_role_menu')
            ->where($condition)->select()->toArray();
        if (!$field) return $menuList;
        $fieldList = [];
        foreach ($menuList as $menu) $fieldList[] = $menu[$field];
        return $fieldList;
    }

    public function hasMenu($roleId, $menuCode): bool
    {
        $condition = [];
        $condition['role_id'] = $roleId;
        $condition['menu_code'] = $menuCode;
        $menu = $this->db('system_role_menu')->where($condition)
            ->find();
        return null === $menu ? false : true;
    }
}