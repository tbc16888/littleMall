<?php
declare (strict_types=1);

namespace core\service\system\menu;

class Order
{
    public static function Make(): array
    {
        return [
            'controller' => 'order', 'module' => 'admin',
            'menu_name' => '订单模块',
            'auth' => 1,
            'children' => [
                [
                    'controller' => 'order', 'menu_name' => '订单管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                        ['action' => 'cancel', 'menu_name' => '取消', 'auth' => 1],
                        ['action' => 'payment', 'menu_name' => '支付', 'auth' => 1],
                        ['action' => 'prepare', 'menu_name' => '备货', 'auth' => 1],
                        ['action' => 'deliver', 'menu_name' => '发货', 'auth' => 1],
                        ['action' => 'refund', 'menu_name' => '退款', 'auth' => 1],
                        ['action' => 'complete', 'menu_name' => '完成', 'auth' => 1],
                    ],
                ],
                [
                    'controller' => 'order.refund', 'menu_name' => '退款管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'audit', 'menu_name' => '审核', 'auth' => 1],
                    ],
                ]
            ]
        ];
    }
}