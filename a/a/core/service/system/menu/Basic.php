<?php
declare (strict_types=1);

namespace core\service\system\menu;

class Basic
{
    public static function Make(): array
    {
        return [
            'controller' => 'basic', 'module' => 'admin',
            'menu_name' => '基础模块',
            'auth' => 0,
            'children' => [
                [
                    'controller' => 'login', 'menu_name' => '系统登录', 'auth' => 0,
                    'action' => 'index',
                    'children' => [
                    ],
                ],
                [
                    'controller' => 'personal', 'menu_name' => '个人操作', 'auth' => 0,
                    'action' => 'index',
                    'children' => [
                        ['action' => 'setprofile', 'menu_name' => '设置资料', 'auth' => 1],
                        ['action' => 'changepassword', 'menu_name' => '修改密码', 'auth' => 1]
                    ],
                ]
            ]
        ];
    }
}