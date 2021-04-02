<?php
declare(strict_types=1);

namespace app\v1\controller\user;

use app\Request;
use think\facade\Db;
use core\base\BaseController;
use core\service\user\UserBalanceService;
use core\service\user\UserService;

class BalanceController extends BaseController
{
    /**
     * 积分记录
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        ;
        $condition = [];
        $condition['user_id'] = session('user_id');
        if (($month = $request->get('month', ''))) {
            $section = \tbc\utils\Tools::getTimeSection('m',
                date('Y-m-d H:i:s', strtotime($month)));
            $condition['change_time'] =
                Db::raw("between '{$section[0]}' and '{$section[1]}'");;
        }

        $service = UserBalanceService::getInstance();
        $total = $service->dbQuery($condition)->count();
        $lists = $service->dbQuery($condition)
            ->page($this->page(), $this->size())
            ->order('id', 'desc')->select()->toArray();
        $group = [];
        foreach ($lists as &$item) {
            $item['change_type_name'] =
                UserBalanceService::$typeMapper[$item['change_type']];
            $month = substr($item['change_time'], 0, 7);
            if (!isset($group[$month])) {
                $section = \tbc\utils\Tools::getTimeSection('m',
                    date('Y-m-d H:i:s', strtotime($month)));
                $group[$month] = ['month' => $month, 'list' => [],
                    'expense' => 0, 'income' => 0];
                $group[$month]['income'] = $service->dbQuery($condition)
                    ->whereBetweenTime('change_time', $section[0], $section[1])
                    ->where('change_amount', '>', 0)->sum('change_amount');
                $group[$month]['expense'] = $service->dbQuery($condition)
                    ->whereBetweenTime('change_time', $section[0], $section[1])
                    ->where('change_amount', '<', 0)->sum('change_amount');
                $group[$month]['expense'] = abs($group[$month]['expense']);
                $group[$month]['expense'] = number_format($group[$month]['expense'], 2, '.', '');
                $group[$month]['income'] = number_format($group[$month]['income'], 2, '.', '');
            }
            $group[$month]['list'][] = $item;
        }
        return finish(0, '获取成功', ['list' => $lists, 'total' => $total,
            'group' => array_values($group)]);
    }

    /**
     * 调整余额
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
        if ($amount < 0 && $userInfo['balance'] < abs($amount)) {
            return finish(1, '用户余额不足');
        }
        UserService::transaction(function () use ($amount, $userId) {
            $remark = $this->request->post('remark', '');
            UserBalanceService::getInstance()->change(
                $userId, floatval($amount), UserBalanceService::CHANGE, $remark);
        });
        return finish(0, '操作成功', $userInfo);
    }
}