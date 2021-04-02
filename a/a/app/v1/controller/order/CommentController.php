<?php
declare(strict_types=1);

namespace app\v1\controller\order;

use app\Request;
use app\v1\validate\GoodsCommentValidate;
use core\base\BaseController;
use core\service\order\OrderService;
use core\service\goods\GoodsCommentService;
use core\service\order\OrderGoodsService;

class CommentController extends BaseController
{
    /**
     * 列表
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return \support\Response|\think\response\Json
     */
    public function index(Request $request)
    {
        $business = new GoodsComment();
        $instance = function () use ($business) {
            return $business->db()->alias('c')->where('c.is_delete', 0)
                ->where('c.create_time', '>', '2021-02-21 23:37:34');
        };
        $total = $instance()->count();
        $field = 'c.star, c.text, c.images, c.create_time, g.goods_name, g.goods_thumb';
        $lists = $instance()->field($field)
            ->join('order_goods og', 'og.order_goods_id = c.order_goods_id')
            ->join('goods g', 'g.goods_id = og.goods_id')
            ->page($this->page(), $this->size())->select()->toArray();
        return finish(0, '获取成功', ['list' => $lists, 'total' => $total]);
    }


    /**
     * 添加
     * @RequestMapping(path="/add", methods="post")
     * @param Request $request
     * @return \support\Response|\think\response\Json
     */
    public function add(Request $request)
    {
        $aComment = $request->post('comment', '[]');
        $aComment = json_decode($aComment, true);
        if (!count($aComment)) return finish(1, '参数错误');
        GoodsCommentService::transaction(function () use ($aComment) {
            $service = new GoodsCommentService();
            foreach ($aComment as $item) {
                $item['user_id'] = session('user_id');
                $this->validate($item, GoodsCommentValidate::class);
                $service->setFormData($item)->addOrUpdate();
                OrderGoodsService::getInstance()->db()
                    ->where('order_goods_id', $item['order_goods_id'])
                    ->where('is_comment', 0)
                    ->limit(1)->save(['is_comment' => 1]);
            }
            $orderSn = $this->request->post('order_sn', '');
            OrderService::getInstance()->db()->
                where('order_sn', $orderSn)->save(['is_comment' => 1]);
        });
        return finish(0, '评论成功');
    }


}