<?php
declare (strict_types=1);

namespace app\v1\controller\user;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsBrowseService;
use core\service\goods\GoodsCollectService;

class BrowseController extends BaseController
{

    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    /**
     * 列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        $condition['uc.user_id'] = session('user_id');
        $service = GoodsBrowseService::getInstance();
        $total = $service->dbQuery($condition, 'uc')
            ->group('uc.goods_id')->count();
        $field = 'g.goods_image, g.goods_name, g.goods_short_name, g.goods_id';
        $field .= ', g.shop_price as final_price, uc.browse_id';
        $field .= ', uc.update_time as browse_time';
        $lists = $service->dbQuery($condition, 'uc')->field($field)
            ->join('goods g', 'g.goods_id = uc.goods_id')
            ->group('g.goods_id')
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

    /**
     * 清空
     * @RequestMapping(path="/clear", methods="any")
     * @param Request $request
     * @return false|string|void
     */
    public function clear(Request $request)
    {
        if (!($collectId = $request->param('collect_id', ''))) {
            return finish(1, '参数错误');
        }
        GoodsCollectService::getInstance()->softDelete($collectId);
        return finish(0, '取消成功');
    }

}
