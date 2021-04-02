<?php
declare(strict_types=1);

namespace app\admin\controller\statistics;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsService;
use core\service\order\OrderGoodsService;

class GoodsController extends BaseController
{
    /**
     * 概况
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $startTime = $request->get('start_time', date('Y-m-d'));
        $endTime = $request->get('end_time', date('Y-m-d'));
        $startTime = date('Y-m-d 00:00:00', strtotime($startTime));
        $endTime = date('Y-m-d 23:59:59', strtotime($endTime));
        $days = ceil((strtotime($endTime) - strtotime($startTime)) / 86400);
        $currSection = [$startTime, $endTime];
        $prevSection = [
            date('Y-m-d H:i:s', intval(strtotime($startTime) - $days * 86400)),
            date('Y-m-d H:i:s', intval(strtotime($endTime) - $days * 86400)),
        ];
        $goodsId = $request->get('goods_id', '');
        $dbInstance = function ($table, $section) use ($goodsId) {
            $instance = \think\facade\Db::name($table);
            if ($goodsId) $instance = $instance->where('goods_id', $goodsId);
            $instance = $instance->whereBetweenTime('create_time',
                $section[0], $section[1]);
            return $instance;
        };
        // 浏览
        $currBrowse = $dbInstance('goods_browse', $currSection)->count();
        $prevBrowse = $dbInstance('goods_browse', $prevSection)->count();
        $response['browse']['total'] = $currBrowse;
        $response['browse']['prev'] = $prevBrowse;
        $response['browse']['comparison'] =
            $prevBrowse ? ($currBrowse - $prevBrowse) / $prevBrowse : 0;
        // 收藏
        $currCollect = $dbInstance('goods_collect', $currSection)->count();
        $prevCollect = $dbInstance('goods_collect', $prevSection)->count();
        $response['collect']['total'] = $currCollect;
        $response['collect']['prev'] = $prevCollect;
        $response['collect']['comparison'] =
            $prevCollect ? ($currCollect - $prevCollect) / $prevCollect : 0;
        // 销售
        $currSale = $dbInstance('order_goods', $currSection)->sum('goods_number');
        $prevSale = $dbInstance('order_goods', $prevSection)->sum('goods_number');
        $response['sale']['total'] = $currSale;
        $response['sale']['prev'] = $prevSale;
        $response['sale']['comparison'] =
            $prevSale ? ($currSale - $prevSale) / $prevSale : 0;
        // 退款
        $conversion = $currBrowse ? $currSale / $currBrowse : 0;
        $response['conversion']['total'] = number_format($conversion, 4);
        $response['conversion']['comparison'] = 0;
        return finish(0, '获取成功', $response);
    }

    /**
     * 统计
     * @RequestMapping(path="/statistics", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function statistics(Request $request)
    {
        $startTime = $request->get('start_time', date('Y-m-01'));
        $endTime = $request->get('end_time', date('Y-m-d'));
        $goodsId = $request->get('goods_id', '');

        $days = [];
        $end = ceil((strtotime($endTime) - strtotime($startTime)) / 86400);
        for ($i = 0; $i <= $end; $i++) {
            $days[] = date('Y-m-d', strtotime($startTime) + 86400 * $i);
        }

        $dbInstance = function ($table, $section) use ($goodsId) {
            $instance = \think\facade\Db::name($table);
            if ($goodsId) $instance = $instance->where('goods_id', $goodsId);
            $instance = $instance->whereBetweenTime('create_time',
                $section[0], $section[1]);
            return $instance;
        };

        $response = [];
        foreach ($days as $day) {
            $browse = $dbInstance('goods_browse', [
                $day . ' 00:00:00', $day . ' 23:59:59'
            ])->count();
            $response['browse'][] = ['date' => $day, 'value' => $browse];
            $sales = $dbInstance('order_goods', [
                $day . ' 00:00:00', $day . ' 23:59:59'
            ])->sum('goods_number');
            $response['sale'][] = ['date' => $day, 'value' => $sales];
        }
        return finish(0, '获取成功', $response);
    }


    /**
     * 排行榜
     * @RequestMapping(path="/ranking", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function ranking(Request $request)
    {
        $startTime = $request->get('start_time', date('Y-m-d 00:00:00'));
        $endTime = $request->get('end_time', date('Y-m-d H:i:s'));

        $buildInstance = function ($table, $goodsId = '') use ($startTime, $endTime) {
            $instance = \think\facade\Db::name($table);
            if ($goodsId) $instance = $instance->where('goods_id', $goodsId);
            return $instance->whereBetweenTime('create_time', $startTime, $endTime);
        };

        $field = 'count(*) as browse, goods_id';
        $lists = $buildInstance('goods_browse')->field($field)
            ->group('goods_id')->order('browse', 'desc')
            ->limit(10)->select()->toArray();

        foreach ($lists as &$goods) {
            $goods['sale'] = $buildInstance('order_goods', $goods['goods_id'])
                ->sum('goods_number');
            $goods['collect'] = $buildInstance('goods_collect', $goods['goods_id'])
                ->count();
            $field = 'goods_name, goods_image';
            $goods = array_merge(GoodsService::getInstance()->db()
                ->where('goods_id', $goods['goods_id'])->field($field)->find(), $goods);
        }
        return finish(0, '获取成功', ['list' => $lists]);
    }

    /**
     * 区域分布
     * @RequestMapping(path="/distribute", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function distribute(Request $request)
    {
        $startTime = $request->get('start_time', date('Y-m-d'));
        $endTime = $request->get('end_time', date('Y-m-d'));
        $startTime = date('Y-m-d 00:00:00', strtotime($startTime));
        $endTime = date('Y-m-d 23:59:59', strtotime($endTime));
        $field = 'sum(goods_number) as value, p.region_name as name';
        $instance = OrderGoodsService::getInstance()->db()->alias('og')->field($field);
        if (($goodsId = $request->get('goods_id', ''))) {
            $instance = $instance->where('goods_id', $goodsId);
        }
        $result = $instance
            ->whereBetweenTime('og.create_time', $startTime, $endTime)
            ->join('order o', 'o.order_sn = og.order_sn')
            ->join('region p', 'p.region_code = o.province')
            ->group('province')->order('value', 'desc')
            ->select()->toArray();
        $search = ['省', '市', '自治区', '回族', '维吾尔', '壮族'];
        foreach ($result as $key => $item) {
            $result[$key]['name'] = str_replace($search, '', $item['name']);
        }
        return finish(0, '获取成功', ['sale' => $result]);
    }


}


