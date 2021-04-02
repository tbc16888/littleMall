<?php
declare(strict_types=1);

namespace app\admin\controller\statistics;

use app\Request;
use core\base\BaseController;
use core\service\user\UserService;

class UserController extends BaseController
{
    /**
     * 统计
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function statistics(Request $request)
    {
        $startTime = $request->get('start_time', date('Y-m-01'));
        $endTime = $request->get('end_time', date('Y-m-d'));

        $days = [];
        $end = ceil((strtotime($endTime) - strtotime($startTime)) / 86400);
        for ($i = 0; $i <= $end; $i++) {
            $days[] = date('Y-m-d', strtotime($startTime) + 86400 * $i);
        }
        $response = [];
        $service = UserService::getInstance();
        foreach ($days as $day) {
            $total = $service->db()
                ->whereBetweenTime('create_time',
                    $day . ' 00:00:00', $day . ' 23:59:59'
                )->count();
            $response['user'][] = ['date' => $day, 'value' => $total];
        }
        return finish(0, '获取成功', $response);
    }
}