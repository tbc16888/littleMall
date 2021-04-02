<?php
declare(strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;

class BasicController extends BaseController
{

    /**
     * 热门关键词
     * @RequestMapping(path="/getHotKeyword", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function getHotKeyword(Request $request)
    {
        $list = ['联想', '连衣裙', '女靴', '电脑'];
        return finish(0, '获取成功', ['list' => $list]);
    }

    /**
     * 退款原因
     * @RequestMapping(path="/getRefundReasonList", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function getRefundReasonList(Request $request)
    {
        return finish(0, '获取成功', ['list' => [
            '不喜欢/不想要', '空包裹', '未按约定定时间发货',
            '快递/物流一直未到达', '快递/物流无跟踪记录', '货物破损已拒签'
        ]]);
    }
}