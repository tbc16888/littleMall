<?php
declare (strict_types=1);

namespace core\service\system\menu;

class UserAccount
{
    public static function Make(): array
    {
        return [
            'controller' => 'user', 'module' => MODULE,
            'menu_name' => '用户模块',
            'auth' => 1,
            'children' => [
                [
                    'controller' => 'user.integral', 'menu_name' => '积分管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'change', 'menu_name' => '调整', 'auth' => 1]
                    ],
                ],
                [
                    'controller' => 'user.balance', 'menu_name' => '余额管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'change', 'menu_name' => '调整', 'auth' => 1]
                    ],
                ],
                [
                    'controller' => 'user.address', 'menu_name' => '地址管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1]
                    ],
                ]
            ]
        ];
    }
}