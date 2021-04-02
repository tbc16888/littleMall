<?php
declare (strict_types=1);

namespace core\service\system\menu;

class Auth
{
    public static function Make()
    {
        return [
            'controller' => 'system', 'module' => 'admin',
            'menu_name' => '系统模块',
            'auth' => 1,
            'children' => [
                [
                    'controller' => 'system.user', 'menu_name' => '用户管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                    ],
                ],
                [
                    'controller' => 'system.role', 'menu_name' => '角色管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                    ],
                ],
            ]
        ];
    }
}