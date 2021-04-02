<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\Request;
use core\base\BaseController;
use app\admin\validate\goods\GoodsValidate;
use core\business\goods\GoodsSnapshot;
use core\business\goods\Goods;
use core\business\goods\GoodsAttr;
use core\business\goods\GoodsCat;
use core\business\goods\GoodsCatRelation;
use core\business\goods\GoodsGallery;
use core\business\goods\GoodsResource;
use core\business\goods\GoodsType;
use core\business\order\OrderGoods;
use core\model\goods\Goods as GoodsModel;
use core\service\goods\GoodsAttributeService;
use core\service\goods\GoodsCategoryService;
use core\service\goods\GoodsImageService;
use core\service\goods\GoodsService;
use core\service\goods\GoodsSkuService;
use core\service\goods\GoodsSnapshotService;

class GoodsController extends BaseController
{
    /**
     * 列表
     * @RequestMapping(path="/index", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        if (($saleStatus = $request->get('sale_status', ''))) {
            $condition['sale_status'] = $saleStatus;
        }
        if (($keyword = $request->get('keyword', ''))) {
            $condition['goods_name'] =
                \think\facade\Db::raw("like '%{$keyword}%'");
        }
        if (($minPrice = floatval($request->get('min_price', '')))) {
            $condition['shop_price'] = \think\facade\Db::raw('> ' . $minPrice);
        }
        if (($maxPrice = floatval($request->get('max_price', '')))) {
            $condition['shop_price'] = \think\facade\Db::raw('< ' . $maxPrice);
        }
        if ($minPrice && $maxPrice) {
            $condition['shop_price'] = \think\facade\Db::raw("between $minPrice and $maxPrice");
        }

        $service = GoodsService::getInstance();
        $total = $service->dbQuery($condition)->count();
        $lists = $service->dbQuery($condition)->order('id', 'desc')
            ->order('sort_weight', 'desc')
            ->order('id', 'desc')
            ->page($this->page(), $this->size())->select()->toArray();
        return finish(0, '获取成功', ['total' => $total, 'list' => $lists]);
    }

    /**
     * 详情
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($goodsId = $request->get('goods_id', ''))) {
            return finish(1, '参数错误');
        }
        $goodsInfo = GoodsService::getInstance()->db()
            ->where('goods_id', $goodsId)->find();
        if (null === $goodsInfo) return finish(1, '商品不存在');

        $goodsInfo['images'] = GoodsImageService::getInstance()
            ->getImages($goodsInfo['goods_id'], $goodsInfo['goods_version']);

        $specAttr = GoodsSkuService::getInstance()->getStockKeepingUnit($goodsInfo['goods_id'], $goodsInfo['goods_version']);
        $goodsInfo['goods_spec'] = array_values($specAttr);
        $goodsInfo['goods_attr'] = GoodsAttributeService::getInstance()
            ->get($goodsInfo['goods_id'], $goodsInfo['goods_version']);

        // 商品分类
        if ($goodsInfo['cat_id']) {
            $catPath = GoodsCategoryService::getInstance()
                ->getPath($goodsInfo['cat_id']);
            $goodsInfo['cat_path'] = $catPath;
        }
        return finish(0, '获取成功', $goodsInfo);
    }

    // sku列表
    public function sku(Request $request)
    {
        if (!($goodsId = $request->get('goods_id', ''))) {
            return finish(1, '参数错误');
        }
        $goodsInfo = Goods::getInstance()->getDetail($goodsId);
        if (null === $goodsInfo) return finish(1, '商品不存在');
        $skuList = Goods::getInstance()->getSkuList($goodsId, $goodsInfo['goods_version']);
        foreach ($skuList as &$sku) {
            $sku['goods_name'] = $goodsInfo['goods_name'];
            $sku['goods_spec'] = OrderGoods::getInstance()->getSpecAttrList($sku['goods_attr']);
        }
        return finish(0, '获取成功', ['list' => $skuList]);
    }

    /**
     * 添加
     * @RequestMapping(path="/add", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function add(Request $request)
    {
        $this->addOrUpdate($request);
        return finish(1, '添加成功');
    }

    protected function addOrUpdate(Request $request)
    {
        $data = $request->getPostParams('cat_id, goods_name, goods_short_name, goods_sn, sale_status, detail_mobile, goods_image, model_id, goods_id, shop_price, market_price, stock, use_integral, use_integral_type');
        $this->validate($data, GoodsValidate::class);
        GoodsService::transaction(function () use ($data) {
            $data['goods_version'] = 1;
            $formData = GoodsService::getInstance()->setFormData($data)
                ->addOrUpdate($this->request->post('goods_id', ''))
                ->getFormData();

            // 商品图片
            $imageUrlList = $this->request->post('images', '[]');
            $imageUrlList = json_decode($imageUrlList, true);
            GoodsImageService::getInstance()->setGallery($formData['goods_id'],
                $formData['goods_version'], $imageUrlList);

            // 商品属性
            $attribute = $this->request->post('goods_attr', '[]');
            $attribute = json_decode($attribute, true);
            GoodsAttributeService::getInstance()->setBaseAttribute(
                $formData['goods_id'],
                $formData['goods_version'],
                $attribute
            );

            // 商品规格
            $skuService = GoodsSkuService::getInstance();
            $goodsSkuList = $this->request->post('goods_spec', '[]');
            $goodsSkuList = json_decode($goodsSkuList, true);
            if (!count($goodsSkuList)) {
                $goodsSkuList[] = GoodsSkuService::createItem($formData['shop_price'], $formData['stock']);
            }
            $skuService->setStockKeepingUnit($formData['goods_id'],
                $formData['goods_version'], $goodsSkuList);
            $rData = $formData;
            \think\facade\Event::trigger('afterGoodsUpdate', $rData);
        });
    }

    /**
     * 编辑
     * @RequestMapping(path="/edit", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function edit(Request $request)
    {
        if ((!$goodsId = $request->post('goods_id', ''))) {
            return finish(1, '参数错误');
        }
        $this->addOrUpdate($request);
        return finish(1, '编辑成功');
    }

    /**
     * 快速编辑
     * @RequestMapping(path="/quickEdit", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function quickEdit(Request $request)
    {
        if ((!$goodsId = $request->post('goods_id', ''))) {
            return finish(1, '参数错误');
        }
        $goodsInfo = GoodsService::getInstance()
            ->db()->where('goods_id', $goodsId)->find();
        if (null === $goodsInfo) return finish(1, '商品不存在');
        $field = 'sort_weight, sale_status, goods_name';
        $data = $request->getPostParams($field);
        GoodsService::getInstance()->setFormData($data)->addOrUpdate($goodsId);
        \think\facade\Event::trigger('afterGoodsUpdate', ['goods_id' => $goodsId,
            'goods_version' => 1]);
        return finish(0, '编辑成功');
    }

    /**
     * 删除
     * @RequestMapping(path="/delete", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if ((!$goodsId = $request->post('goods_id', ''))) {
            return finish(1, '参数错误');
        }
        GoodsService::getInstance()->softDelete($goodsId);
        return finish(0, '删除成功');
    }

    /**
     * 修改销售状态
     * @RequestMapping(path="/delete", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function changeSaleStatus(Request $request)
    {
        if ((!$goodsId = $request->post('goods_id', ''))) {
            return finish(1, '参数错误');
        }
        $status = $request->post('status', 1);
        if (!in_array($status, [Goods::GOODS_START_SALE, Goods::GOODS_CLOSE_SALE])) {
            return finish(1, '状态错误');
        }
        if (!Goods::getInstance()->getDbInstance()->where('goods_id', $goodsId)
            ->limit(1)->update(['sale_status' => $status])) {
            return finish(1, '操作失败');
        }
        return finish(0, '操作成功');
    }

    /**
     * 商品历史版本
     * @RequestMapping(path="/version", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function version(Request $request)
    {
        if ((!$goodsId = $request->get('goods_id', ''))) {
            return finish(1, '参数错误');
        }
        $instance = GoodsSnapshotService::getInstance()->db();
        $instance->where('goods_id', $goodsId);
        $lists = $instance->page($this->page(), $this->size())
            ->order('id', 'desc')->select()->toArray();
        $total = $instance->count();
        return finish(0, '获取成功', ['total' => $total, 'list' => $lists]);
    }
}
