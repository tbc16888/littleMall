<?php
declare (strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;
use core\service\user\UserService;

class UserController extends BaseController
{
    /**
     * 注册
     * @RequestMapping(path="/register", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function register(Request $request)
    {
        if ((!$mobile = $request->post('mobile', ''))) {
            return finish(1, '手机号为空');
        }
        if ((!$code = $request->post('code', ''))) {
            return finish(1, '验证码为空');
        }
        return finish(0, '注册成功');
    }

    /**
     * 获取信息
     * @RequestMapping(path="info", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function info(Request $request)
    {
        if (!($userId = $request->get('user_id', ''))) {
            return finish(1, '参数错误');
        }
        $userInfo = UserService::getInstance()->getInfo($userId);
        if (null === $userInfo) return finish(1, '用户不存在');
        unset($userInfo['password']);
        return finish(0, '获取成功', $userInfo);
    }

    /**
     * 获取或更新个人信息
     * @RequestMapping(path="/profile", methods="get, post")
     * @param Request $request
     * @return false|string|void
     */
    public function profile(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->getPostParams('avatar, nick_name');
            UserService::getInstance()->setFormData($data)
                ->addOrUpdate(session('user_id'));
            return finish(0, '操作成功');
        } else {
            $userInfo = UserService::getInstance()->getInfo(session('user_id'));
            if (null === $userInfo) return finish(401, '');
            unset($userInfo['password']);
            return finish(0, '获取成功', $userInfo);
        }
    }
}
