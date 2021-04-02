<?php
declare(strict_types=1);

namespace app\v1\controller\goods;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsCollectService;

class CollectController extends BaseController
{
    // 登录认证
    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    /**
     * 添加
     * @RequestMapping(path="/add", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function add(Request $request)
    {
        if ((!$goodsId = $request->post('goods_id', ''))) {
            return finish(1, '参数错误');
        }
        $rData = [];
        $rData['goods_id'] = $goodsId;
        $rData['user_id'] = session('user_id');
        $info = GoodsCollectService::getInstance()->dbQuery($rData)->find();
        if (null === $info) $info['collect_id'] = '';
        GoodsCollectService::getInstance()->setFormData($rData)
            ->addOrUpdate($info['collect_id']);
        return finish(0, '操作成功');
    }

    /**
     * 删除
     * @RequestMapping(path="/delete", methods="any")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if ((!$goodsId = $request->param('goods_id', ''))) {
            return finish(1, '参数错误');
        }
        $rData = [];
        $rData['goods_id'] = $goodsId;
        $rData['user_id'] = session('user_id');
        GoodsCollectService::getInstance()->dbQuery($rData)->delete();
        return finish(0, '操作成功');
    }
}