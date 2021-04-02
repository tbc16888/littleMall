<?php
declare (strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;

class IndexController extends BaseController
{

    /**
     * 首页
     * @RequestMapping(path="/index", methods="any")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {

    }
}
