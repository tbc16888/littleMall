<?php
declare (strict_types=1);

namespace app\admin\controller\goods;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsCommentService;

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
        $condition = [];
        $service = GoodsCommentService::getInstance();
        $total = $service->dbQuery($condition, 'c')->count();
        $field = 'c.score, c.text, c.images, c.create_time, g.goods_name';
        $field .= ', g.goods_image, u.nick_name, u.user_id, u.avatar';
        $lists = $service->dbQuery($condition, 'c')->field($field)
            ->join('order_goods og', 'og.order_goods_id = c.order_goods_id')
            ->join('goods g', 'g.goods_id = og.goods_id')
            ->leftJoin('user u', 'u.user_id = c.user_id')
            ->order('c.id', 'desc')
            ->page($this->page(), $this->size())->select()->toArray();
        return finish(0, '获取成功', ['list' => $lists, 'total' => $total]);
    }
}
