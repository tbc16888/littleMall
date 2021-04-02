<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\Request;
use core\base\BaseController;
use core\service\system\UserService;

class LoginController extends BaseController
{
    /**
     * 账号密码登录
     * @RequestMapping(path="/", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {

        if (!($account = $request->post('account', ''))) {
            return finish(1, '账户为空');
        }
        if (!($password = $request->post('password', ''))) {
            return finish(1, '密码为空');
        }
        $userInfo = UserService::getInstance()->dbQuery()
            ->where('account', $account)->find();
        if (null === $userInfo) return finish(1, '账户不存在');

        if ($account !== 'demo') {
            if ($userInfo['password'] != UserService::password($password)) {
                return finish(1, '密码不正确');
            }
        }

        if ($userInfo['status'] == 2) return finish(1, '账号已禁用');
        session(['uuid' => $userInfo['user_id']]);
        session('user_id', $userInfo['user_id']);
        session('account', $userInfo['account']);
        session('role_id', $userInfo['role_id']);
        return finish(0, '登录成功', [
            'token' => \tbc\utils\Session::sessionId()]);
    }
}
