<?php
declare(strict_types=1);

namespace app\v1\middleware;

use think\facade\Request;

class InitAfterRoute
{
    public function handle($request, \Closure $next)
    {
        defined('MODULE') or define('MODULE', strtolower(App('http')->getName()));
        defined('CONTROLLER') or define('CONTROLLER', strtolower($request->controller()));
        defined('ACTION') or define('ACTION', strtolower($request->action()));
        defined('DOMAIN') or define('DOMAIN', $request->domain());


        $header = Request::header();
        if (!isset($header['authorization'])) $header['authorization'] = '';
        if ($header['authorization'] === 'null') $header['authorization'] = '';
        if ($header['authorization'] === 'undefined') $header['authorization'] = '';
        if ($header['authorization'] && strtolower(CONTROLLER) !== 'login') {
            \tbc\utils\Session::sessionId($header['authorization']);
        }
        defined('CHANNEL') or define('CHANNEL', $header['channel'] ?? '');
        return $next($request);
    }
}