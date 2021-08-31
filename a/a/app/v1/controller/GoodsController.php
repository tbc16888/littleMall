<?php
declare (strict_types=1);

namespace app\v1\controller;

use think\facade\Db;
use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsService;
use core\service\goods\GoodsBrowseService;
use core\service\goods\GoodsImageService;
use core\service\goods\GoodsCollectService;
use core\service\goods\GoodsFrontModuleService;

class GoodsController extends BaseController
{

    /**
     * 商品列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $instance = GoodsService::getInstance();

        $condition = [];
        $condition['sale_status'] = 1;
        if (($keyword = $request->get('keyword', ''))) {
            $condition['goods_name'] = Db::raw("like '%{$keyword}%'");
        }
        $total = $instance->dbQuery($condition)->count();
        $field = 'goods_image, goods_id, goods_name, shop_price';
        $lists = $instance->dbQuery($condition)
            ->page($this->page(), $this->size())->field($field)
            ->order('sort_weight', 'desc')->select()->toArray();
        foreach ($lists as &$goods) $goods['final_price'] = $goods['shop_price'];
        return finish(0, '获取成功', ['list' => $lists,
            'total' => $total]);
    }


    /**
     * 商品详情
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($goodsId = $request->get('goods_id', ''))) {
            return finish(1, '参数错误');
        }

        $goods = GoodsService::getInstance()->dbQuery()
            ->where('goods_id', $goodsId)->find();
        if (null === $goods) return finish(1, '商品不存在或已删除');

        $modules = (new GoodsFrontModuleService())
            ->setGoodsInfo($goods)->basic()->attribute()->spec()->sku()
            ->banner()->countdown()->slider()->operation()->module();

        $response = [];
        foreach ($modules as $module) $response[$module['type']] = $module['data'];

        // 收藏判断
        $isCollect = GoodsCollectService::getInstance()->isCollect($goods['goods_id'],
            session('user_id'));
        $response['basic']['field']['is_collect'] = intval($isCollect);
        GoodsBrowseService::getInstance()->inc($goodsId, session('user_id'));
        return finish(0, '获取成功', $response);
    }

    /**
     * 筛选列表
     * @RequestMapping(path="/filter", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function filter(Request $request)
    {
        $filterList = [];
        return finish(0, '', ['list' => $filterList]);
    }
}
