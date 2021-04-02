<?php
declare(strict_types=1);

namespace app\v1\controller\order;

use app\Request;
use think\facade\Db;
use core\base\BaseController;
use core\constant\order\OrderStatus;
use core\service\order\OrderService;

class StatisticsController extends BaseController
{
    // 权限认证
    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    /**
     *
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        $condition['o.user_id'] = session('user_id');
        $condition['o.is_delete'] = 0;
        $condition['o.order_status'] = Db::raw("< " . OrderStatus::CANCEL);
        $service = OrderService::getInstance();
        $waitPay = $service->dbQuery($condition, 'o')->where($condition)->count();

        $status = implode(',', [
            OrderStatus::PAYMENT, OrderStatus::PREPARE
        ]);
        $condition['o.order_status'] = Db::raw("in ($status)");
        $waitSend = $service->dbQuery($condition, 'o')->where($condition)->count();

        $condition['o.order_status'] = Db::raw(">= " . OrderStatus::COMPLETE);
        $condition['o.is_comment'] = 0;
        $waitComment = $service->dbQuery($condition, 'o')->where($condition)->count();

        $statistics['waitPay'] = $waitPay;
        $statistics['waitComment'] = $waitComment;
        $statistics['waitSend'] = $waitSend;
        return finish(0, '获取成功', $statistics);
    }
}