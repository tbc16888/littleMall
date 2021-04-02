<?php
declare(strict_types=1);

namespace app\admin\middleware;

class Init
{
    public function handle($request, \Closure $next)
    {
        // 跨域设置
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: authorization');
        return $next($request);
    }
}