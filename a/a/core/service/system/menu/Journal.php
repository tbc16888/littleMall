<?php
declare (strict_types=1);

namespace core\service\system\menu;

class Journal
{
    public static function Make(): array
    {
        return [
            'controller' => 'journal', 'module' => 'admin',
            'menu_name' => '日志模块',
            'auth' => 0,
            'children' => [
                [
                    'controller' => 'journal.operation', 'menu_name' => '操作日志', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'detail', 'menu_name' => '详情', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                    ],
                ]
            ]
        ];
    }
}