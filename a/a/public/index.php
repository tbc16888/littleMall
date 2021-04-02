<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

defined('PROJECT_PATH') or define('PROJECT_PATH', dirname(dirname(dirname(__DIR__))));
defined('PROGRAM_PATH') or define('PROGRAM_PATH', dirname(__DIR__));
defined('THINK_APP_PATH') or define('THINK_APP_PATH', dirname(__DIR__));
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

require THINK_APP_PATH . '/vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
