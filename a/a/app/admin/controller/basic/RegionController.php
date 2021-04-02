<?php
declare(strict_types=1);

namespace app\admin\controller\basic;

use app\Request;
use core\base\BaseController;
use core\service\basic\RegionService;

class RegionController extends BaseController
{
    /**
     * 联动地区列表
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        if (($parentId = intval($request->get('pid', 0)))) {
            $regionInfo = RegionService::getInstance()->info($parentId);
            $parentId = $regionInfo['region_id'];
        }
        $condition = [];
        $condition['parent_id'] = $parentId;
        $lists = RegionService::getInstance()->db()->where($condition)
            ->select()->toArray();
        $result = [];
        foreach ($lists as $item) {
            $result[] = [
                'label' => $item['region_name'], 'value' => $item['region_code'],
                // 'leaf' => $item['region_type'] > 1 ? 0 : $item['child_total']
                'leaf' => $item['child_total']
            ];
        }
        return finish(0, '获取成功', ['list' => $result, 'total' => count($lists)]);
    }
}