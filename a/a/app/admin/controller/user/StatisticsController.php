<?php
declare(strict_types=1);

namespace app\admin\controller\user;

use app\Request;
use core\base\BaseController;
use core\service\coupon\UserCouponService;
use core\service\user\UserAddressService;

class StatisticsController extends BaseController
{
    /**
     * 用户统计
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        if (!($userId = $request->get('user_id', ''))) {
            return finish(1, '参数错误');
        }
        $statistics = [];
        $condition = [];
        $condition['user_id'] = $userId;
        $statistics['coupon'] = UserCouponService::getInstance()->dbQuery()
            ->where($condition)->count();
        $statistics['address'] = UserAddressService::getInstance()
            ->dbQuery($condition)->count();
        return finish(0, '获取成功', $statistics);
    }
}