<?php
declare (strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;
use app\v1\validate\CartValidate;
use core\constant\goods\GoodsConstant;
use core\service\cart\CartService;
use core\service\cart\CartFrontModuleService;

class CartController extends BaseController
{

    protected string $field = 'goods_id, goods_number, goods_sku_id, goods_sku_key, is_checked, is_direct';

    // 登录认证
    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    public function initialize()
    {
        CartService::getInstance()->setUserId(session('user_id'));
    }

    /**
     * 列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        if (!session('user_id')) return finish(401, '请登录', []);
        $condition = [];
        $condition['is_direct'] = 0;
        $lists = CartService::getInstance()->getList($condition);
        foreach ($lists as &$item) {
            $item['reason'] = '';
            if ($item['sale_status'] != GoodsConstant::START_SALE)
                $item['reason'] = '已下架';
            if ($item['goods_number'] > $item['stock']) $item['reason'] = '补货中';
            $item['is_invalid'] = $item['reason'] ? 1 : 0;
        }

        $cartFrontModule = new CartFrontModuleService();
        $items = $cartFrontModule->items($lists);
        $response = [];
        $response['items'] = $items;
        $response['submit'] = $cartFrontModule->submit($items['data']['list']);
        return finish(0, '获取成功', $response);
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
        $this->validate($data, CartValidate::class);
        $cartId = CartService::getInstance()->setFormData($data)->addOrUpdate()
            ->getTableUniqueId();
        return finish(0, '操作成功', ['cart_id' => $cartId]);
    }

    /**
     * 编辑
     * @RequestMapping(path="/edit", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function edit(Request $request)
    {
        $data = $request->getPostParams($this->field);
        $this->validate($data, CartValidate::class);
        CartService::getInstance()->setFormData($data)->addOrUpdate();
        return finish(0, '操作成功');
    }

    /**
     * 删除购物车
     * @RequestMapping(path="/delete", methods="get,post,delete")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if (!($cartId = $request->param('cart_id', ''))) {
            return finish(1, '参数错误');
        }
        CartService::getInstance()->db()->whereIn('cart_id', $cartId)
            ->update(['is_delete' => 1]);
        return finish(0, '操作成功');
    }

    /**
     * 改变选择状态
     * @RequestMapping(path="/changeSelectStatus", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function changeSelectStatus(Request $request)
    {
        $status = intval($request->post('status', 1));
        CartService::getInstance()->changeCheckStatus($status,
            $request->post('cart_id', ''));
        return finish(0, '操作成功');
    }

}
