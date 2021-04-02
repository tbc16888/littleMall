<?php
declare(strict_types=1);

namespace app\index\middleware;

class InitAfterRoute
{
    public function handle(\app\Request $request, \Closure $next)
    {
        defined('MODULE') or define('MODULE', strtolower(App('http')->getName()));
        defined('CONTROLLER') or define('CONTROLLER', strtolower($request->controller()));
        defined('ACTION') or define('ACTION', strtolower($request->action()));
        defined('DOMAIN') or define('DOMAIN', $request->domain());
        return $next($request);
    }

}