<?php
declare (strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;
use EasyWeChat\Factory;
use core\service\basic\MobileCodeService;
use core\service\user\UserService;
use core\service\user\UserBindService;

class LoginController extends BaseController
{

    // 登录成功回调
    private function loginCallback($userInfo)
    {
        if ($userInfo['status'] != 1) return finish(1, '账号已禁用');
        session('user_id', $userInfo['user_id']);
        session('open_id', $userInfo['open_id'] ?? '');
        return finish(0, '登陆成功', [
            'token' => \tbc\utils\Session::$sessionId,
            'is_sync_info' => intval(strpos($userInfo['nick_name'], '用户') !== false),
//            'is_sync_info' => 1,
        ]);
    }

    /**
     * 账号密码
     * @RequestMapping(path="index", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        if (!($mobile = $request->post('mobile', ''))) return finish(1, '手机号为空');
        if (!\tbc\utils\Validate::isChinaMobile($mobile)) return finish(1, '手机号格式不正确');
        if (!($password = $request->post('password', ''))) return finish(1, '密码为空');
        $userInfo = UserService::getInstance()->info('', $mobile);
        if (null === $userInfo) return finish(1, '账号不存在');
        if ($userInfo['password'] != UserService::password($password))
            return finish(1, '密码错误');
        return $this->loginCallback($userInfo);
    }

    /**
     * 短信验证码登录
     * @RequestMapping(path="/sms", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function sms(Request $request)
    {
        if ((!$mobile = $request->post('mobile', ''))) {
            return finish(1, '手机号为空');
        }
        if (!\tbc\utils\Validate::isChinaMobile($mobile)) {
            return finish(1, '手机号格式不正确');
        }
        if ((!$code = $request->post('code', ''))) {
            return finish(1, '验证码为空');
        }
        MobileCodeService::getInstance()->verify($mobile, $code, 'login');
        $userInfo = UserService::getInstance()->info('', $mobile);
        if (null === $userInfo) {
            $userId = UserService::getInstance()->setFormData([
                'mobile' => $mobile,
                'password' => substr($mobile, -6)
            ])->addOrUpdate()->getTableUniqueId();
            $userInfo = UserService::getInstance()->info($userId);
        }
        return $this->loginCallback($userInfo);
    }


    // 第三方平台
    protected function thirdPartyPlatform(string $openId, int $platform, $unionId = '')
    {
        $bindInfo = UserBindService::getInstance()->info($openId, $platform);
        if (null === $bindInfo) {// 注册成新用户
            $bindInfo = UserService::transaction(function () use (
                $openId, $platform, $unionId
            ) {
                $userId = UserService::getInstance()->setFormData([
                    'password' => strval(mt_rand(100000, 999999)),
                    'nick_name' => '用户' . mt_rand(1000, 9999)
                ])->addOrUpdate()->getTableUniqueId();
                UserBindService::getInstance()->bind($userId, $platform,
                    $openId, ['union_id' => $unionId]);
                return ['user_id' => $userId];
            });
        }
        return $bindInfo;
    }


    /**
     * 微信登录
     * @RequestMapping(path="/wx", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function wx(Request $request)
    {
        if (!($openId = $request->post('open_id'))) {
            return finish(1, '参数错误');
        }
        $bindInfo = $this->thirdPartyPlatform($openId, UserBindService::WX,
            $request->post('union_id', ''));
        $userInfo = UserService::getInstance()->getInfo($bindInfo['user_id']);
        $userInfo['open_id'] = $openId;
        return $this->loginCallback($userInfo);
    }

    /**
     * QQ登录
     * @RequestMapping(path="/qq", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function qq(Request $request)
    {
        if (!($openId = $request->post('open_id'))) {
            return finish(1, '参数错误');
        }
        $bindInfo = $this->thirdPartyPlatform($openId, UserBindService::QQ,
            $request->post('union_id', ''));
        $userInfo = UserService::getInstance()->getInfo($bindInfo['user_id']);
        $userInfo['open_id'] = $openId;
        return $this->loginCallback($userInfo);
    }

    /**
     * 微信小程序登录
     * @RequestMapping(path="/wxMiniProgram", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function wxMiniProgram(Request $request)
    {
        if (!($code = $request->post('code', ''))) {
            return finish(1, 'code 为空');
        }
        if (!($iv = $request->post('iv', ''))) {
            return finish(1, 'iv 为空');
        }
        if (!($encryptedData = $request->post('encryptedData', ''))) {
            return finish(1, '加密数据为空');
        }

        $app = Factory::miniProgram(['app_id' => env('wx.mini_program_app_id'),
            'secret' => env('wx.mini_program_secret')]);
        $auth = $app->auth->session($code);

        if (isset($auth['errmsg'])) return finish(1, $auth['errmsg']);

        $bindInfo = UserBindService::getInstance()->info($auth['openid'], UserBindService::WX_MINI_PROGRAM);
        if (null === $bindInfo) {
            $decryptedData = $app->encryptor->decryptData($auth['session_key'], $iv,
                $encryptedData);
            $bindInfo = UserService::transaction(function () use ($decryptedData, $auth) {

                $userId = UserService::getInstance()->setFormData([
                    'password' => 'P' . mt_rand(100000, 999999),
                    'mobile' => $decryptedData['phoneNumber']
                ])->addOrUpdate()->getTableUniqueId();
                UserBindService::getInstance()->bind($userId,
                    UserBindService::WX_MINI_PROGRAM,
                    $auth['openid']);
                return ['user_id' => $userId];
            });
        }
        $userInfo = UserService::getInstance()->getInfo($bindInfo['user_id']);
        $userInfo['open_id'] = $auth['openid'];
        return $this->loginCallback($userInfo);
    }

}
