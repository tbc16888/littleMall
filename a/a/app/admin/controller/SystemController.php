<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\Request;
use core\base\BaseController;
use core\service\system\ConfigService;

class SystemController extends BaseController
{
    /**
     * 获取配置项
     * @RequestMapping(path="/getConfig", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function getConfig(Request $request)
    {
        $configList = ConfigService::getInstance()->getConfig();
        return finish(0, '获取成功', $configList);
    }

    /**
     * 设置配置项
     * @RequestMapping(path="/setConfig", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function setConfig(Request $request)
    {
        ConfigService::getInstance()->setConfig($request->param());
        return finish(0, '设置成功');
    }
}