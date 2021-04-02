<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\Request;
use core\base\BaseController;

class LogoutController extends BaseController
{
    /**
     * 退出
     * @RequestMapping(path="/", methods="get, post, delete")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        \tbc\utils\Session::clear();
        return finish(0, '退出成功');
    }
}
