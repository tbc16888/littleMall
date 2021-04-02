<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\Request;
use core\base\BaseController;
use core\service\system\UserService;

class PersonalController extends BaseController
{
    protected $userId = '';

    public function initialize()
    {
        $this->userId = session('user_id');
    }

    /**
     * 信息
     * @RequestMapping(path="/information", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function information(Request $request)
    {
        $userInfo = UserService::getInstance()->getInfo(session('user_id'));
        if (null === $userInfo) return finish(1, '数据不存在或已删除');
        unset($userInfo['password']);
        return finish(0, '获取成功', $userInfo);
    }

    /**
     * 设置个人资料
     * @RequestMapping(path="/setProfile", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function setProfile(Request $request)
    {
        $data = $request->getParams('nick_name');
        UserService::getInstance()->setFormData($data)
            ->addOrUpdate(session('user_id'));
        return finish(0, '操作成功');
    }

    /**
     * 修改密码
     * @RequestMapping(path="/changePassword", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function changePassword(Request $request)
    {
        if (!($oldPassword = $request->post('old_password', ''))) {
            return finish(1, '旧密码为空');
        }
        if (!($newPassword = $request->post('new_password', ''))) {
            return finish(1, '新密码为空');
        }

        $userInfo = UserService::getInstance()->db()
            ->where('user_id', session('user_id'))->find();
        if (null === $userInfo) return finish(1, '用户已删除');
        if ($userInfo['password'] !== UserService::password($oldPassword)) {
            return finish(1, '旧密码不正确');
        }
        $result = UserService::getInstance()->setFormData([
            'password' => $newPassword
        ])->addOrUpdate(session('user_id'));
        return finish(0, '修改成功');
    }

    public function menu(Request $request)
    {
        return finish(0, '获取成功', []);
    }
}
