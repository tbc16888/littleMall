<?php
declare(strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;
use core\business\system\Config;

class SystemController extends BaseController
{
    /**
     * 获取系统配置
     * @RequestMapping(path="/getConfig", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function getConfig(Request $request)
    {
        if ((!$code = $request->get('code', ''))) {
            return finish(1, '参数错误');
        }
        $config = Config::getInstance()->getValue($code, '');
        return finish(0, '获取成功', $config);
    }
}