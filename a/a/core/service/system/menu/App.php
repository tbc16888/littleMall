<?php
declare (strict_types=1);

namespace core\service\system\menu;

class App
{
    public static function Make(): array
    {
        return [
            'controller' => 'app', 'module' => 'admin',
            'menu_name' => '应用管理',
            'auth' => 1,
            'children' => [
                [
                    'controller' => 'app.version', 'menu_name' => '版本管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                    ],
                ],
                [
                    'controller' => 'app.template', 'menu_name' => '模版管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                        ['action' => 'copy', 'menu_name' => '复制', 'auth' => 1],
                    ],
                ]
            ]
        ];
    }
}