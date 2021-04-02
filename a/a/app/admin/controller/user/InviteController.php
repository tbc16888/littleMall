<?php
declare(strict_types=1);

namespace app\admin\controller\user;

use app\Request;
use core\base\BaseController;
use core\service\coupon\UserCouponService;

class InviteController extends BaseController
{
    /**
     *
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {

    }
}