<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\Request;
use app\admin\validate\coupon\CouponValidate;
use core\base\BaseController;
use core\service\coupon\CouponService;
use core\service\coupon\CouponGoodsService;
use core\service\coupon\UserCouponService;
use core\service\user\UserService;

class  CouponController extends BaseController
{
    protected array $field = [
        'coupon_name', 'coupon_total', 'minimum', 'discount', 'quota',
        'status', 'use_scope', 'coupon_desc', 'distribution_method', 'effective_type',
        'effective_days', 'effective_start_time', 'effective_end_time',
        'release_start_time', 'release_end_time'
    ];


    /**
     * 列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $service = CouponService::getInstance();
        $condition = [];
        $total = $service->dbQuery()->count();
        $lists = $service->dbQuery()->page($this->page(), $this->size())
            ->order('id', 'desc')->select()->toArray();
//        foreach ($lists as &$item) {
//            $condition = [];
//            $condition['coupon_id'] = $item['coupon_id'];
//            $item['distribution_total'] = UserCoupon::getInstance()->getTotal($condition);
//            $item['distribution_total'] = 100000000;
//            $item['distribute_method_name'] = Coupon::$distribution[$item['distribution_method']];
//        }
        return finish(0, '获取成功', ['total' => $total,
            'list' => $lists]);
    }

    /**
     * 详情
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($couponId = $this->request->get('coupon_id'))) {
            return finish(1, '参数错误');
        }
        $couponInfo = CouponService::getInstance()->dbQuery()
            ->where('coupon_id', $couponId)->find();
        if (null === $couponInfo) return finish(1, '优惠券不存在');
        $couponInfo['distribution'] = 0;
        return finish(0, '获取成功', $couponInfo);
    }

    /**
     * 商品
     * @RequestMapping(path="/goods", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function goods(Request $request)
    {
        if (!($couponId = $this->request->get('coupon_id'))) {
            return finish(1, '参数错误');
        }
        $instance = CouponGoodsService::getInstance()->db()->alias('cg')
            ->where('coupon_id', $couponId);
        $field = 'g.goods_name, g.goods_image, g.shop_price, g.sale_status, g.goods_id';
        $lists = $instance->field($field)
            ->join('goods g', 'cg.goods_id = g.goods_id')
            ->select()->toArray();
        $total = $instance->count();
        return finish(0, '获取成功', ['total' => $total, 'list' => $lists]);
    }

    /**
     * 添加
     * @RequestMapping(path="/add", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function add(Request $request)
    {
        $data = $request->getPostParams($this->field);
        $this->validate($data, CouponValidate::class);
        CouponService::transaction(function () use ($data) {
            $couponId = CouponService::getInstance()->setFormData($data)
                ->addOrUpdate()->getTableUniqueId();
            CouponGoodsService::getInstance()->setGoods($couponId,
                explode(',', $this->request->post('goods')));
        });
        return finish(0, '添加成功');
    }

    /**
     * 编辑
     * @RequestMapping(path="/edit", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function edit(Request $request)
    {
        if (!($couponId = $this->request->post('coupon_id'))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams($this->field);
        $this->validate($data, CouponValidate::class);
        CouponService::transaction(function () use ($data, $couponId) {
            CouponService::getInstance()->setFormData($data)->addOrUpdate($couponId);
            CouponGoodsService::getInstance()->setGoods($couponId,
                explode(',', $this->request->post('goods')));
        });
        return finish(0, '编辑成功');
    }

    /**
     * 用户
     * @RequestMapping(path="/user", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function user(Request $request)
    {
        $condition = [];
        $userService = UserService::getInstance();
        $field = 'u.nick_name, u.account, u.mobile, u.user_id';
        $count = $userService->dbQuery($condition, 'u')->count();
        $lists = $userService->dbQuery($condition, 'u')->field($field)
            ->page($this->page(), $this->size())
            ->select()->toArray();
        foreach ($lists as &$item) {
            $item['received_count'] = $item['received_count'] ?? 0;
        }
        return finish(0, '获取成功', ['total' => $count, 'list' => $lists]);
    }

    /**
     * 发放
     * @RequestMapping(path="/send", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function send(Request $request)
    {
        if (!($couponId = $request->post('coupon_id', ''))) {
            return finish(1, '参数错误');
        }

        if (!($userId = $request->post('user_id', ''))) {
            return finish(1, '参数错误');
        }
        CouponService::getInstance()->sendToUser($couponId, $userId);
        return finish(0, '发放成功');
    }

    /**
     * 已发放
     * @RequestMapping(path="/received", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function received(Request $request)
    {
        $condition = [];
        if (($couponId = $request->get('coupon_id', ''))) {
            $condition['uc.coupon_id'] = $couponId;
        }
        if (($status = intval($request->get('status', 0)))) {
            $condition['uc.status'] = $status;
        }
        if (($userId = $request->get('user_id', ''))) {
            $condition['uc.user_id'] = $userId;
        }
        $service = UserCouponService::getInstance();
        $total = $service->dbQuery($condition, 'uc')->count();
        $field = 'uc.*, c.coupon_name, u.account, u.nick_name';
        $lists = $service->dbQuery($condition, 'uc')->field($field)
            ->join('coupon c', 'uc.coupon_id = c.coupon_id')
            ->join('user u', 'uc.user_id = u.user_id')
            ->page($this->page(), $this->size())->select()->toArray();
        return finish(0, '获取成功', ['total' => $total, 'list' => $lists]);
    }
}