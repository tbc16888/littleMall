<?php
declare (strict_types=1);

namespace core\service\system\menu;

class SystemConfig
{
    public static function Make(): array
    {
        return [
            'controller' => 'system', 'module' => 'admin',
            'menu_name' => '系统模块',
            'auth' => 1,
            'children' => [
                [
                    'controller' => 'system', 'menu_name' => '系统配置', 'auth' => 1,
                    'children' => [
                        ['action' => 'getConfig', 'menu_name' => '读取', 'auth' => 1],
                        ['action' => 'setConfig', 'menu_name' => '设置', 'auth' => 1],
                    ],
                ]
            ]
        ];
    }
}