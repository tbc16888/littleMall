<?php
declare (strict_types=1);

namespace app\admin\controller\basic;

use app\Request;
use core\base\BaseController;
use core\service\shipping\ShippingService;

class ShippingController extends BaseController
{
    public function index()
    {
        $lists = ShippingService::getInstance()->dbQuery()->select()->toArray();
        return finish(0, '获取成功', ['list' => $lists, 'total' => count($lists)]);
    }
}
