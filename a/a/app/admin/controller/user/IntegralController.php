<?php
declare(strict_types=1);

namespace app\admin\controller\user;

use app\Request;
use core\base\BaseController;
use core\service\user\UserIntegralService;
use core\service\user\UserService;

class IntegralController extends BaseController
{
    /**
     * 积分记录
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $instance = function () use ($request) {
            $instance = UserIntegralService::getInstance()->db();
            $condition = [];
            if (($userId = $request->get('user_id', ''))) {
                $condition['user_id'] = $userId;
            }
            $instance->where($condition);
            return $instance;
        };
        $total = $instance()->count();
        $lists = $instance()->page($this->page(), $this->size())
            ->order('id', 'desc')->select()->toArray();
        foreach ($lists as &$item) {
            $item['change_type_name'] =
                UserIntegralService::$typeMapper[$item['change_type']];
        }
        return finish(0, '获取成功', ['list' => $lists, 'total' => $total]);
    }

    /**
     * 调整积分
     * @RequestMapping(path="/change", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function change(Request $request)
    {
        if (!($userId = $request->post('user_id', ''))) {
            return finish(1, '参数错误');
        }
        if (!($amount = $request->post('amount', 0))) {
            return finish(1, '调整数量不能为0');
        }

        $userInfo = UserService::getInstance()->getInfo($userId);
        if (null === $userInfo) return finish(1, '用户不存在');
        if ($amount < 0 && $userInfo['integral'] < abs($amount)) {
            return finish(1, '用户积分不足');
        }
        UserService::transaction(function () use ($amount, $userId) {
            $remark = $this->request->post('remark', '');
            UserIntegralService::getInstance()->change(
                $userId, intval($amount), UserIntegralService::CHANGE, $remark);
        });
        return finish(0, '操作成功', $userInfo);
    }
}