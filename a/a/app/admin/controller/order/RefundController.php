<?php
declare(strict_types=1);

namespace app\admin\controller\order;

use app\Request;
use core\base\BaseController;
use core\service\order\OrderRefundService;
use core\service\order\OrderFrontModuleService;

class RefundController extends BaseController
{
    /**
     * 列表
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        if (($auditStatus = $request->get('audit_status', ''))) {
            $condition['or.audit_status'] = $auditStatus;
        }
        $service = OrderRefundService::getInstance();
        $total = $service->dbQuery($condition, 'or')->count();
        $field = 'or.*, o.order_amount, o.order_status';
        $lists = $service->dbQuery($condition, 'or')->field($field)
            ->join('order o', 'o.order_sn = or.order_sn')
            ->order('or.id', 'desc')
            ->page($this->page(), $this->size())->select()->toArray();
        $order = [];
        $moduleService = new OrderFrontModuleService();
        foreach ($lists as $item) {
            $moduleItem = [];
            $moduleList = $moduleService->clear()->setOrder($item)
                ->status()->goods($item['order_goods_id'])->getModule();
            $moduleList[] = ['type' => 'basic', 'data' => [
                'field' => $item
            ]];
            foreach ($moduleList as $module) $moduleItem[$module['type']] = $module;
            $order[] = $moduleItem;
        }
        return finish(0, '获取成功', ['list' => $order, 'total' => $total]);
    }

    /**
     * 详情
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($refundId = $request->get('refund_id', ''))) {
            return finish(1, '参数错误');
        }
        $info = OrderRefundService::getInstance()->db()
            ->where('refund_id', $refundId)->find();
        return finish(0, '获取成功', $info);
    }


    /**
     * 退款审核
     * @RequestMapping(path="/audit", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function audit(Request $request)
    {
        if (!($refundId = $request->post('refund_id', ''))) {
            return finish(1, '参数错误');
        }
        $refundMethod = intval($request->post('refund_method', 1));
        $refundAmount = floatval($request->post('refund_amount'));
        $remark = $request->post('remark', '', 'trim');
        $status = intval($request->post('status', 2));
        OrderRefundService::getInstance()->setAdminId(session('user_id'))
            ->audit($refundId, $status, $refundMethod, $refundAmount, $remark);
        return finish(0, '操作成功');
    }
}