<?php
declare (strict_types=1);

namespace core\service\system\menu;

class Goods
{
    public static function Make(): array
    {
        return [
            'controller' => 'goods', 'module' => 'admin',
            'menu_name' => '商城模块',
            'auth' => 1,
            'children' => [
                [
                    'controller' => 'goods', 'menu_name' => '商品管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                        ['action' => 'quickedit', 'menu_name' => '快速编辑', 'auth' => 1],
                    ],
                ],
                [
                    'controller' => 'goods.brand', 'menu_name' => '品牌管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1]
                    ],
                ],
                [
                    'controller' => 'goods.category', 'menu_name' => '分类管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                        ['action' => 'setmodel', 'menu_name' => '设置模型', 'auth' => 1],
                    ],
                ],
                [
                    'controller' => 'goods.model', 'menu_name' => '模型管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'add', 'menu_name' => '添加', 'auth' => 1],
                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1]
                    ],
                ],
                [
                    'controller' => 'goods.gather', 'menu_name' => '采集管理', 'auth' => 1,
                    'children' => [
                        ['action' => 'index', 'menu_name' => '列表', 'auth' => 1],
                        ['action' => 'start', 'menu_name' => '采集', 'auth' => 1],
//                        ['action' => 'edit', 'menu_name' => '编辑', 'auth' => 1],
                        ['action' => 'delete', 'menu_name' => '删除', 'auth' => 1],
                        ['action' => 'detail', 'menu_name' => '入库', 'auth' => 1]
                    ],
                ],
            ]
        ];
    }
}