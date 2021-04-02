<?php
declare(strict_types=1);

namespace app\v1\controller\app;

use app\Request;
use core\base\BaseController;
use core\service\app\VersionService;

class VersionController extends BaseController
{
    /**
     * 最后发布版本
     * @RequestMapping(path="/last", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function last(Request $request)
    {
        $condition = [];
        $platform = strtolower($request->get('platform', '', 'trim'));
        $condition['platform'] = $platform;
        $condition['status'] = 1;
        $versionInfo = VersionService::getInstance()->dbQuery($condition)
            ->order('id', 'desc')->find();
        if (null === $versionInfo) return finish(1, '不存在数据');
        return finish(0, '获取成功', $versionInfo);
    }
}