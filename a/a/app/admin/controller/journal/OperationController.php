<?php
declare (strict_types=1);

namespace app\admin\controller\journal;

use app\Request;
use think\facade\Db;
use core\base\BaseController;
use core\service\system\OperationService;

class OperationController extends BaseController
{
    /**
     * 操作日志
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        if (($datetime1 = $request->get('datetime_1', ''))) {
            $condition['sop.create_time'] =
                Db::raw("sop.create_time >= '{$datetime1}'");
        }
        if (($datetime2 = $request->get('datetime_2', ''))) {
            $condition['sop.create_time'] =
                Db::raw("sop.create_time <= '{$datetime2}'");
        }
        if ($datetime1 && $datetime2) {
            $raw = " BETWEEN '{$datetime1}' AND '{$datetime2}'";
            $condition['sop.create_time'] = Db::raw($raw);
        }
        $total = OperationService::getInstance()->dbQuery($condition, 'sop')->count();
        $lists = OperationService::getInstance()->getList($condition,
            $this->page(), $this->size());
        return finish(0, '获取成功', ['list' => $lists,
            'total' => $total]);
    }


    /**
     * 操作日志详情
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($logId = $request->get('log_id', ''))) {
            return finish(1, '参数错误');
        }
        $condition = [];
        $condition['log_id'] = $logId;
        $logInfo = OperationService::getInstance()->getList($condition, 1, 1);
        return finish(0, '获取成功', $logInfo);
    }
}

