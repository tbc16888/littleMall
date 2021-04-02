<?php
declare(strict_types=1);

namespace app\admin\controller\statistics;

use app\Request;
use core\base\BaseController;
use core\service\order\OrderService;
use core\constant\order\OrderStatus;

class OrderController extends BaseController
{
    /**
     * 统计
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $cycle = $request->get('cycle', 'day', 'trim');
        $symbol = substr($cycle, 0, 1);
        $curr = date('Y-m-d');
        $prev = date('Y-m-d', time() - 86400);
        if ('w' === $symbol) $prev = date('Y-m-d', time() - 7 * 86400);
        if ('m' === $symbol) $prev = date('Y-m-d', strtotime(date('Y-m-t')) - 31 * 86400);
        $currSection = \tbc\utils\Tools::getTimeSection($symbol, $curr);
        $prevSection = \tbc\utils\Tools::getTimeSection($symbol, $prev);

        // 总订单
        $select = OrderService::getInstance()->dbQuery()
            ->whereBetween('order_time', $currSection)->count();
        $before = OrderService::getInstance()->dbQuery()
            ->whereBetween('order_time', $prevSection)->count();
        $comparison = $before ? ($select - $before) / $before : 0;
        $total = ['total' => $select, 'before' => $before, 'comparison' => $comparison];

        $condition = [];
        $condition['order_status'] = OrderStatus::CANCEL;
        $select = OrderService::getInstance()->dbQuery($condition)
            ->whereBetween('order_time', $currSection)->count();
        $before = OrderService::getInstance()->dbQuery($condition)
            ->whereBetween('order_time', $prevSection)->count();
        $comparison = $before ? ($select - $before) / $before : 0;
        $cancel = ['total' => $select, 'before' => $before, 'comparison' => $comparison];

        $response = [];
        $response['all'] = $total;
        $response['cancel'] = $cancel;
        return finish(0, '获取成功', $response);
    }

    public function statistics(Request $request)
    {
        $startTime = $request->get('start_time', date('Y-m-01'));
        $endTime = $request->get('end_time', date('Y-m-d'));
        $goodsId = $request->get('goods_id', '');

        $days = [];
        $end = ceil((strtotime($endTime) - strtotime($startTime)) / 86400);
        for ($i = 0; $i <= $end; $i++) {
            $days[] = date('Y-m-d', strtotime($startTime) + 86400 * $i);
        }
        $response = [];
        foreach ($days as $day) {
            $order = OrderService::getInstance()->dbQuery()
                ->whereBetween('order_time', [$day . ' 00:00:00', $day . ' 23:59:59'])
                ->count();
            $response['order'][] = ['date' => $day, 'value' => $order];
            $cancel = OrderService::getInstance()->dbQuery()
                ->whereBetween('order_time', [$day . ' 00:00:00', $day . ' 23:59:59'])
                ->where('order_status', OrderStatus::CANCEL)->count();
            $response['cancel'][] = ['date' => $day, 'value' => $cancel];
        }
        return finish(0, '获取成功', $response);
    }
}


