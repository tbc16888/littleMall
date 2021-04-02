<?php
declare (strict_types=1);

namespace app\v1\controller\user;

use app\Request;
use core\base\BaseController;
use core\service\coupon\CouponService;
use core\service\coupon\UserCouponService;

class CouponController extends BaseController
{

    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    /**
     * 列表
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        if (($status = $request->get('status', '')))
            $condition['uc.status'] = $status;
        $condition['uc.user_id'] = session('user_id');
        $service = UserCouponService::getInstance();
        $total = $service->db()->alias('uc')->where($condition)->count();
        $lists = $service->getList($condition, $this->page(), $this->size());
        foreach ($lists as &$item) {
            $item['discount'] = intval($item['discount']);
            $item['minimum'] = intval($item['minimum']);
            $item['effective_start_time'] = substr($item['effective_start_time'], 0, 11);
            $item['effective_end_time'] = substr($item['effective_end_time'], 0, 11);
            $item['use_scope_text'] = '全部商品可用';
            if ($item['use_scope'] == 2) $item['use_scope_text'] = '部分商品可用';
            $item['is_used'] = intval($item['status'] == UserCouponService::USED);
            $item['is_expired'] = intval($item['status'] == UserCouponService::EXPIRED);
        }
        return finish(0, '获取成功', ['total' => $total,
            'list' => $lists]);
    }


    /**
     * 领取
     * @RequestMapping(path="/receive", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function receive(Request $request)
    {
        if (!($couponId = $request->post('coupon_id', ''))) {
            return finish(1, '参数错误');
        }
        CouponService::getInstance()->sendToUser($couponId, session('user_id'));
        return finish(0, '领取成功');
    }


    public function notAvailable()
    {
        return finish(0, '获取成功', ['list' => []]);
    }
}
