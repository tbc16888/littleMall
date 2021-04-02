<?php
declare (strict_types=1);

namespace core\service\system\menu;

class Statistics
{
    public static function Make(): array
    {
        return [
            'controller' => 'statistics', 'module' => 'admin',
            'menu_name' => '统计分析',
            'auth' => 1,
            'children' => [
                [
                    'controller' => 'statistics.goods', 'menu_name' => '商品统计', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '概况', 'auth' => 1],
                        ['action' => 'statistics', 'menu_name' => '统计', 'auth' => 1],
                        ['action' => 'ranking', 'menu_name' => '排行', 'auth' => 1],
                        ['action' => 'distribute', 'menu_name' => '区域部分', 'auth' => 1],
                    ],
                ],
                [
                    'controller' => 'statistics.order', 'menu_name' => '订单统计', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '概况', 'auth' => 1],
                        ['action' => 'statistics', 'menu_name' => '统计', 'auth' => 1]
                    ],
                ],
                [
                    'controller' => 'statistics.user', 'menu_name' => '用户统计', 'auth' => 1,
                    'children' => [
//                        ['action' => 'index', 'menu_name' => '概况', 'auth' => 1],
                        ['action' => 'statistics', 'menu_name' => '统计', 'auth' => 1]
                    ],
                ]
            ]
        ];
    }
}