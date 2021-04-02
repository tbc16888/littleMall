<?php
declare(strict_types=1);

namespace app\v1\controller\user;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsBrowseService;
use core\service\goods\GoodsCollectService;

class StatisticsController extends BaseController
{

    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    /**
     *
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        $condition['user_id'] = session('user_id');
        $browse = GoodsBrowseService::getInstance()->dbQuery($condition)
            ->group('goods_id')->count();
        $collect = GoodsCollectService::getInstance()->dbQuery($condition)
            ->count();
        $statistics = [];
        $statistics['browse'] = $browse;
        $statistics['collect'] = $collect;
        return finish(0, '获取成功', $statistics);
    }
}