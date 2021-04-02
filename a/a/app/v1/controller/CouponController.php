<?php
declare (strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;
use core\service\coupon\CouponService;
use core\service\coupon\UserCouponService;
use think\facade\Db;

class CouponController extends BaseController
{
    /**
     * 列表
     * @RequestMapping(path="/", methods="any")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        $condition['status'] = 1;
        $time = date('Y-m-d H:i:s');
        $condition['release_start_time'] = Db::raw("< '{$time}'");
        $condition['release_end_time'] = Db::raw("> '{$time}'");
        $service = CouponService::getInstance();
        $total = $service->dbQuery($condition)->count();
        $lists = $service->dbQuery($condition)
            ->page($this->page(), $this->size())->select()->toArray();
        foreach ($lists as &$item) {
            $item['is_received'] = UserCouponService::getInstance()
                ->isReceived($item['coupon_id'], session('user_id'));
            $item['discount'] = intval($item['discount']);
            $item['minimum'] = intval($item['minimum']);
            $item['effective_start_time'] = substr($item['effective_start_time'], 0, 11);
            $item['effective_end_time'] = substr($item['effective_end_time'], 0, 11);
            $item['use_scope_text'] = '全部商品可用';
            if ($item['use_scope'] == 2) $item['use_scope_text'] = '部分商品可用';
        }
        return finish(0, '获取成功', ['list' => $lists,
            'total' => $total]);
    }
}
