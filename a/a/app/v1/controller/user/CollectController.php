<?php
declare (strict_types=1);

namespace app\v1\controller\user;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsCollectService;

class CollectController extends BaseController
{
    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    /**
     * 列表
     * @RequestMapping(path="detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        $condition['uc.user_id'] = session('user_id');
        $service = GoodsCollectService::getInstance();
        $total = $service->dbQuery($condition, 'uc')->count();
        $field = 'g.goods_image, g.goods_name, g.goods_short_name, g.goods_id';
        $field .= ', g.shop_price as final_price, uc.collect_id';
        $lists = $service->dbQuery($condition, 'uc')->field($field)
            ->join('goods g', 'g.goods_id = uc.goods_id')
            ->page($this->page(), $this->size())->select()->toArray();
        return finish(0, '获取成功', ['list' => $lists, 'total' => $total]);
    }

    /**
     * 取消
     * @RequestMapping(path="/delete", methods="any")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if (!($collectId = $request->param('collect_id', ''))) {
            return finish(1, '参数错误');
        }
        GoodsCollectService::getInstance()->softDelete($collectId);
        return finish(0, '取消成功');
    }
}
