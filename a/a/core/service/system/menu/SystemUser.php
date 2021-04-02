<?php
declare (strict_types=1);

namespace core\service\system\menu;

class User
{
    public static function Make(): array
    {
        return [
            'controller' => 'system.user', 'module' => MODULE,
            'menu_name' => '用户模块',
            'auth' => 1,
            'children' => [
                [
                    'controller' => 'user', 'menu_name' => '用户管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                        ['action' => 'detail', 'menu_name' => '详情', 'auth' => 1],
                        ['action' => 'statistics', 'menu_name' => '统计', 'auth' => 1],
                    ],
                ]
            ]
        ];
    }
}