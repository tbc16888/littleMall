<?php
declare(strict_types=1);

namespace app\index\middleware;

class Init
{
    public function handle(\app\Request $request, \Closure $next)
    {
        return $next($request);
    }
}