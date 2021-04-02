<?php
declare(strict_types=1);

namespace app\v1\controller\app;

use app\Request;
use core\base\BaseController;

class PushController extends BaseController
{
    /**
     * 绑定推送的ClientId
     * @RequestMapping(path="/bindClientId", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function bindClientId(Request $request)
    {
        return finish(0, '操作成功');
    }
}