<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\Request;
use core\base\BaseController;
use core\service\file\UploadService;

class UploadController extends BaseController
{
    /**
     * 上传配置
     * @RequestMapping(path="/config", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function config(Request $request)
    {
        $callback = DOMAIN . '/v1/upload/qiNiuUploadCallback';
        $config = UploadService::getInstance()
            ->getQiNiuYunUploadConfig($callback, 'little-mall');
        return finish(0, '获取成功', $config);
    }
}
