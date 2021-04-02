<?php
declare (strict_types=1);

namespace core\service\system\menu;

class Promotion
{
    public static function Make(): array
    {
        return [
            'controller' => 'promotion', 'module' => 'admin',
            'menu_name' => '促销管理',
            'auth' => 1,
            'children' => [
                [
                    'controller' => 'coupon', 'menu_name' => '优惠券', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                    ],
                ]
            ]
        ];
    }
}