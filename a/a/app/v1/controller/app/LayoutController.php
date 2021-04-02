<?php
declare(strict_types=1);

namespace app\v1\controller\app;

use app\Request;
use core\base\BaseController;
use core\service\app\TemplateService;

class LayoutController extends BaseController
{
    /**
     * 指定页面模块
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        if (($page = $request->get('page', ''))) {
            if (strpos($page, '/') !== 0) $page = '/' . $page;
        }
        $condition = [];
        $condition['page'] = $page;
        $condition['status'] = 1;
        $template = TemplateService::getInstance()->dbQuery($condition)
            ->order('id', 'desc')->find();
        $lists = [];
        if (null !== $template) {
            if ($template['module']) {
                $module = json_decode($template['module'], true);
                foreach ($module as &$item) $item['module'] = $item['type'];
                $lists = $module;
            }
            if ($template['config']) {
                $config = json_decode($template['config'], true);
                array_unshift($lists, ['module' => 'background',
                    'config' => $config]);
            }
        }
        return finish(0, '获取成功', ['list' => $lists]);
    }
}