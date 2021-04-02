<?php
declare(strict_types=1);

namespace app\v1\middleware;

class Auth
{
    public function handle($request, \Closure $next)
    {
        if (!session('user_id')) return finish(401, '请登录');
        return $next($request);
    }
}