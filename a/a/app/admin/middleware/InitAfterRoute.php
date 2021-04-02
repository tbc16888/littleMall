<?php
declare(strict_types=1);

namespace app\admin\middleware;

use core\exception\BusinessException;
use core\exception\PermissionException;
use core\service\system\OperationService;
use core\service\system\MenuService;
use core\service\system\RoleService;

class InitAfterRoute
{
    public function handle(\app\Request $request, \Closure $next)
    {
        defined('MODULE') or define('MODULE', strtolower(App('http')->getName()));
        defined('CONTROLLER') or define('CONTROLLER', strtolower($request->controller()));
        defined('ACTION') or define('ACTION', strtolower($request->action()));
        defined('DOMAIN') or define('DOMAIN', $request->domain());

        $header = $request->header();
        $header['authorization'] = $header['authorization'] ?? '';
        if ($header['authorization'] === 'null') $header['authorization'] = '';
        if ($header['authorization'] === 'undefined') $header['authorization'] = '';
        if ($header['authorization'] && strtolower(CONTROLLER) !== 'login') {
            \tbc\utils\Session::sessionId($header['authorization']);
        }

        if (!session('user_id') && !in_array(CONTROLLER, ['login', 'index'])) {
            return finish(401, '请登录');
        }

        if (($result = $this->checkOperationPermission($request))) {
            return $result;
        }
        $response = $next($request);
        $this->addOperation($request, $response);
        return $response;
    }


    // 校验操作权限
    public function checkOperationPermission($request): ?string
    {
        $superAccount = config('app.supper_admin');
        if (!is_array($superAccount)) $superAccount = [];
        if (in_array(session('account'), $superAccount)) return '';
        if (in_array(CONTROLLER, ['login', 'logout', 'personal', 'index'])) return '';

        $menuCode = implode(':', array_filter([MODULE, CONTROLLER, ACTION]));
        if (!RoleService::getInstance()->hasMenu(session('role_id'), $menuCode)) {
            $menuInfo = MenuService::getInstance()->db()
                ->where('menu_code', $menuCode)->find();
            if (null !== $menuInfo) {
                $message = ['「', $menuInfo['full_menu_name'], '」无权限操作'];
                throw new PermissionException(implode('', $message), 403);
            }
        }
        return null;
    }


    // 记录操作记录
    function addOperation(\app\Request $request, \think\Response $response): bool
    {
        try {
            if ($request->isGet() || !session('user_id')) return true;

            $params = $request->param();
            if (isset($params['password'])) unset($params['password']);

            $rData = [];
            $rData['module'] = MODULE;
            $rData['controller'] = CONTROLLER;
            $rData['action'] = ACTION;
            $rData['params'] = json_encode($params, JSON_UNESCAPED_UNICODE);
            $rData['ipv4'] = \tbc\utils\Tools::GetClientIPv4();
            $rData['user_id'] = intval(session('user_id'));
            $rData['code'] = $response->getData()['code'] ?? 1;
            $rData['message'] = $response->getData()['message'] ?? '';
            OperationService::getInstance()->setFormData($rData)->addOrUpdate();
            return true;
        } catch (\Exception $e) {

        }
    }
}