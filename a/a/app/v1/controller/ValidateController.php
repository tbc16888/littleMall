<?php
declare (strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;
use app\v1\validate\MobileCodeValidate;
use core\service\basic\MobileCodeService;

class ValidateController extends BaseController
{
    /**
     * 发送手机验证码
     * @RequestMapping(path="/sendMobileCode", methods="get, post")
     * @param Request $request
     * @return false|string|void
     */
    public function sendMobileCode(Request $request)
    {
        $data = $request->getPostParams('mobile, action');
        $this->validate($data, MobileCodeValidate::class);
        $code = mt_rand(100000, 999999);
        // if ($data['mobile'] == '15577984914') return finish(1, '账号已锁定');
        MobileCodeService::getInstance()->send($data['mobile'],
            $data['action'], $code);
        return finish(0, '操作成功', ['code' => $code,
            'timer' => 60, 'exists' => 2]);
    }

    /**
     * 验证手机验证码
     * @RequestMapping(path="/verifyMobileCode", methods="get, post")
     * @param Request $request
     * @return false|string|void
     */
    public function verifyMobileCode(Request $request)
    {
        $data = $request->getParams('mobile, action, code');
        $this->validate($data, MobileCodeValidate::class);
        if ((!($code = $request->param('code', '')))) {
            return finish(1, '验证码为空');
        }
        MobileCodeService::getInstance()->verify($data['mobile'],
            $data['action'], $code);
        return finish(0, '验证码正确');
    }
}
