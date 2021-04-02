<?php
declare(strict_types=1);

namespace app\admin\controller\statistics;

use app\Request;
use core\base\BaseController;
use core\constant\order\OrderStatus;
use core\service\user\UserService;
use core\service\order\OrderService;

class DashboardController extends BaseController
{
    /**
     * 统计
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $curr = date('Y-m-d');
        $prev = date('Y-m-d', time() - 86400);
        $currSection = \tbc\utils\Tools::getTimeSection('d', $curr);
        $prevSection = \tbc\utils\Tools::getTimeSection('d', $prev);

        $condition = [];
        $condition['is_delete'] = 0;

        // 用户
        $select = UserService::getInstance()->dbQuery($condition)
            ->whereBetween('create_time', $currSection)->count();
        $before = UserService::getInstance()->dbQuery($condition)
            ->whereBetween('create_time', $prevSection)->count();
        $comparison = $before ? ($select - $before) / $before : 0;
        $user = ['total' => $select, 'before' => $before, 'comparison' => $comparison];

        // 订单
        $select = OrderService::getInstance()->dbQuery($condition)
            ->whereBetween('order_time', $currSection)->count();
        $before = OrderService::getInstance()->dbQuery($condition)
            ->whereBetween('order_time', $prevSection)->count();
        $comparison = $before ? ($select - $before) / $before : 0;
        $order = ['total' => $select, 'before' => $before, 'comparison' => $comparison];

        // 销售
        $select = OrderService::getInstance()->dbQuery($condition)
            ->whereBetween('order_time', $currSection)->sum('order_amount');
        $before = OrderService::getInstance()->dbQuery($condition)
            ->whereBetween('order_time', $prevSection)->sum('order_amount');
        $comparison = $before ? ($select - $before) / $before : 0;
        $sales = ['total' => $select, 'before' => $before, 'comparison' => $comparison];
        $response = ['user' => $user, 'order' => $order, 'sales' => $sales];
        return finish(0, '获取成功', $response);
    }


    /**
     * 订单
     * @RequestMapping(path="/order", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function order(Request $request)
    {
        $order = OrderService::getInstance()->db()->count();
        $waitPay = OrderService::getInstance()->db()
            ->where('order_status', '<', OrderStatus::CANCEL)->count();
        $waitDeliver = OrderService::getInstance()->db()
            ->whereBetween('order_status', [OrderStatus::PAYMENT, OrderStatus::DELIVER])->count();
        $cancelled = OrderService::getInstance()->db()
            ->where('order_status', OrderStatus::CANCEL)->count();
        $refunding = OrderService::getInstance()->db()
            ->where('order_status', OrderStatus::REFUNDING)->count();
        return finish(0, '获取成功', ['wait_pay' => $waitPay,
            'wait_ready' => $cancelled, 'wait_deliver' => $waitDeliver,
            'refunding' => $refunding]);
    }
}