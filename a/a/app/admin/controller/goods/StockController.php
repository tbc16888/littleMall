<?php
declare (strict_types=1);

namespace app\admin\controller\goods;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsSkuService;

class StockController extends BaseController
{
    /**
     * 库存
     * @RequestMapping(path="/stock", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
//        $service = GoodsSkuService::getInstance();
//        $field = 'g.goods_id, g.goods_name, g.goods_image, g.sale_status';
//        $field .= ', gs.stock, gs.shop_price, gs.goods_sku_text';
//        $total = $service->dbQuery()->alias('gs')->count();
//        $lists = $service->dbQuery()->alias('gs')->field($field)
//            ->join('goods_sku gs', 'gs.goods_id = g.goods_id and gs.goods_version = g.goods_version')
//            ->page($this->page(), $this->size())
//            ->order('gs.stock', 'asc')->order('g.id', 'desc')
//            ->select()->toArray();
//        return finish(0, '获取成功', ['total' => $total, 'list' => $lists]);
    }
}
