<?php
declare(strict_types=1);

namespace app\v1\controller\basic;

use app\Request;
use core\base\BaseController;
use core\service\basic\RegionService;

class RegionController extends BaseController
{
    /**
     * 获取省市区联动数据
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        if (($regionCode = $request->get('pid', ''))) {
            $regionInfo = RegionService::getInstance()->info($regionCode);
            $regionCode = $regionInfo['region_id'];
        }
        $condition = [];
        $condition['parent_id'] = $regionCode;
        $lists = RegionService::getInstance()->db()->where($condition)
            ->select()->toArray();
        $region = [];
        foreach ($lists as $item) {
            $region[] = ['value' => $item['region_code'], 'label' => $item['region_name'], 'leaf' => $item['child_total']];
        }
        return finish(0, '获取成功', ['list' => $region,
            'total' => count($lists)]);
    }
}