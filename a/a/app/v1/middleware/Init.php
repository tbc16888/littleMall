<?php
declare(strict_types=1);

namespace app\v1\middleware;

use think\facade\Request;

class Init
{
    public function handle($request, \Closure $next)
    {
        // 跨域设置
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: authorization');
        header('Access-Control-Allow-Headers: channel');

        return $next($request);
    }

}