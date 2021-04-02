<?php
declare(strict_types=1);

namespace app\admin\controller\order;

use app\Request;
use core\base\BaseController;
use core\constant\order\OrderStatus;
use core\service\order\OrderOperationService;

class OperationController extends BaseController
{
    /**
     * 列表
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $instance = OrderOperationService::getInstance()->db()->alias('op');
        if (($orderSn = $request->get('order_sn', ''))) {
            $instance->where('op.order_sn', $orderSn);
        }
        $field = 'op.operate, op.create_time, op.user_id';
        $field .= ', su.nick_name as admin_nick_name, su.user_id as system_user_id';
        $lists = $instance->field($field)
            ->leftJoin('system_user su', 'su.user_id = op.system_user_id')
            ->order('op.id', 'desc')
            ->page($this->page(), 100)->select()->toArray();
        foreach ($lists as &$item) {
            $item['operate_name'] = OrderStatus::$actionMapper[$item['operate']];
            $item['nick_name'] = $item['user_id'] ? '用户' : '';
        }
        return finish(0, '获取成功', ['list' => $lists]);
    }
}