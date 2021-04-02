<?php
declare (strict_types=1);

namespace core\service\system\menu;

class File
{
    public static function Make(): array
    {
        return [
            'controller' => 'system', 'module' => 'admin',
            'menu_name' => '系统模块',
            'auth' => 0,
            'children' => [
                [
                    'controller' => 'file', 'menu_name' => '文件管理', 'auth' => 0,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                        ['action' => 'move', 'menu_name' => '移动', 'auth' => 1]
                    ],
                ]
            ]
        ];
    }
}