<?php
declare (strict_types=1);

namespace app\v1\controller;

use think\Exception;
use app\Request;
use core\base\BaseController;
use core\constant\order\OrderStatus;
use core\service\user\UserBalanceService;
use core\service\order\OrderService;
use core\service\order\OrderPaymentService;

class PayController extends BaseController
{
    public static function getYuRunPaySDK(): \Yurun\PaySDK\AlipayApp\SDK
    {
        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID = env('alipay.app_id');
        $params->sign_type = 'RSA2';
        $params->appPrivateKey = env('alipay.merchant_private_key');
        return new \Yurun\PaySDK\AlipayApp\SDK($params);
    }

    /**
     * 支付列表
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        if (!($orderSn = $request->get('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $condition = [];
        $condition['order_sn'] = $orderSn;
        $orderInfo = OrderService::getInstance()->dbQuery($condition)->find();
        $balance = UserBalanceService::getInstance()->current(session('user_id'));
        $balanceNote = '当前可用余额' . $balance . '(需要测试可后台调整金额)';
        $lists = [];
        $lists[] = [
            'name' => '余额支付',
            'icon' => '/static/icon/balance.png',
            'note' => $balanceNote,
            'code' => 'balance',
            'disabled' => $orderInfo['order_amount'] > $balance,
            'api' => implode('/', [DOMAIN, MODULE, CONTROLLER, 'balance'])
        ];

        $lists[] = [
            'name' => '支付宝支付',
            'icon' => '/static/icon/alipay.png',
            'code' => 'alipay',
            'note' => CHANNEL !== 'app' ? '非APP端暂不支持' : '',
            'color' => '#F30',
            'disabled' => CHANNEL !== 'app',
            'api' => implode('/', [DOMAIN, MODULE, CONTROLLER, 'alipay'])
        ];

        if (CHANNEL === 'app') {

            $lists[] = [
                'name' => '微信支付',
                'icon' => '/static/icon/wxpay.png',
                'code' => 'wxpay',
                'api' => implode('/', [DOMAIN, MODULE, CONTROLLER, 'wxMiniProgram'])
            ];
        }

        if (CHANNEL === 'weixin') {
            $lists[] = [
                'name' => '微信支付',
                'icon' => '/static/icon/wxpay.png',
                'code' => 'wxpay',
                'api' => implode('/', [DOMAIN, MODULE, CONTROLLER, 'wxpay'])
            ];
        }
        return finish(0, '获取成功', ['list' => $lists]);
    }

    /**
     * 余额支付
     * @RequestMapping(path="/balance", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function balance(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $orderInfo = OrderPaymentService::getInstance()->checkAndGetInfo($orderSn);
        OrderService::transaction(function () use ($orderInfo) {
            $rData = [];
            $rData['pay_type'] = OrderPaymentService::BALANCE;
            OrderService::getInstance()->setUserId($orderInfo['user_id'])
                ->payment($orderInfo['order_sn'], $rData);
            UserBalanceService::getInstance()->change($orderInfo['user_id'],
                $orderInfo['order_amount'] * -1, UserBalanceService::CONSUME,
                '订单支付' . $orderInfo['order_sn']);
        });
        return finish(0, '支付成功');
    }

    /**
     * 支付宝APP支付
     * @RequestMapping(path="/alipay", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function alipay(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $orderInfo = OrderPaymentService::getInstance()->checkAndGetInfo($orderSn);
        $callbackUrl = implode('/', [DOMAIN, MODULE, CONTROLLER, 'alipayCallback']);
        $request = new \Yurun\PaySDK\AlipayApp\App\Params\Pay\Request;
        $request->notify_url = $callbackUrl;
        $request->businessParams->out_trade_no = $orderSn;
        $request->businessParams->total_amount = $orderInfo['order_amount']; // 价格
        $request->businessParams->subject = '订单支付'; // 商品标题
        static::getYuRunPaySDK()->prepareExecute($request, $url, $data);
        return finish(0, '获取成功', http_build_query($data));
    }

    /**
     * 支付宝回调
     * @RequestMapping(path="/alipayCallback", methods="get, post")
     * @param Request $request
     * @return false|string|void
     */
    public function alipayCallback(Request $request)
    {
        if (static::getYuRunPaySDK()->verifyCallback($request->param())) {
            // 通知验证成功，可以通过POST参数来获取支付宝回传的参数
        } else {
            return finish(1, '');
        }
    }

    /**
     * 支付宝测试
     * @RequestMapping(path="/aliTest", methods="get, post")
     * @param Request $request
     * @return false|string|void
     */
    public function aliTest(Request $request)
    {
        if (!($orderSn = $request->post('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $orderInfo = OrderPaymentService::getInstance()->checkAndGetInfo($orderSn);
        $payInfo = [];
        $payInfo['pay_type'] = OrderPaymentService::ALIPAY_APP;
        $payInfo['out_trade_no'] = implode('', [
            date('YmdHid'),
            '04330000',
            mt_rand(10000, 99999)
        ]);
        OrderService::getInstance()->setUserId($orderInfo['user_id'])
            ->payment($orderSn, $payInfo);
        return finish(0, '支付成功');
    }


    /**
     * 微信小程序支付
     * @RequestMapping(path="/wxMiniProgram", methods="get, post")
     * @param Request $request
     * @return false|string|void
     */
    public function wxMiniProgram(Request $request)
    {
        if (!($orderSn = $request->param('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $orderInfo = OrderPaymentService::getInstance()->checkAndGetInfo($orderSn);
        $totalFee = $orderInfo['order_amount'] * 100;
        if (env('env') === 'dev') $totalFee = 1;
        $callbackUrl = implode('/', [DOMAIN, MODULE, CONTROLLER, 'wxCallback']);
        $app = OrderPaymentService::getInstance()->getEasyWeChatWxPaymentApp();
        $result = $app->order->unify([
            'body' => '支付订单',
            'out_trade_no' => $orderInfo['order_sn'],
            'total_fee' => $totalFee,
            'notify_url' => $callbackUrl,
            'trade_type' => 'JSAPI',
            'openid' => session('open_id'),
        ]);
        /*if ($result['result_code'] != 'SUCCESS')
            return finish(1, $result['err_code_des']);*/
        if ($request['return_code'] != 'SUCCESS') {
            return finish(1, $result['return_msg']);
        }
        return finish(0, '获取成功',
            $app->jssdk->bridgeConfig($result['prepay_id'], false));
    }

    /**
     * 微信APP支付
     * @RequestMapping(path="/wx", methods="get, post")
     * @param Request $request
     * @return false|string|void
     */
    public function wx(Request $request)
    {
        return finish(1, '获取成功', '');
    }

    /**
     * 微信回调
     * @RequestMapping(path="/wxCallback", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function wxCallback(Request $request)
    {
        $response = OrderPaymentService::getInstance()->getEasyWeChatWxPaymentApp()->handlePaidNotify(function ($data, $fail) {
            $orderInfo = Order::getInstance()->getInfo($data['out_trade_no']);
            if (null === $orderInfo) $fail('订单不存在');
            $payInfo = [];
            $payInfo['pay_type'] = OrderPaymentService::WECHAT_MINI_PROGRAM;
            $payInfo['out_trade_no'] = $data['transaction_id'];
            try {
                Order::getInstance()->setUserId($orderInfo['user_id'])
                    ->setOrderSn($orderInfo['order_sn'])->payment($payInfo);
            } catch (Exception $e) {
                $fail($e->getMessage());
            }
            return true;
        });
        return $response->send();
    }

    /**
     * 模拟微信支付
     * @RequestMapping(path="/wxTest", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function wxTest(Request $request)
    {
        if (!($orderSn = $request->param('order_sn', ''))) {
            return finish(1, '参数错误');
        }
        $transactionId = '4200000828' . date('Ymd') . time();
        $orderInfo = OrderService::getInstance()->db()->where('order_sn', $orderSn)
            ->find();
        if (null === $orderInfo) return finish(1, '订单不存在');
        $payInfo = [];
        $payInfo['pay_type'] = OrderPaymentService::WECHAT_MINI_PROGRAM;
        $payInfo['out_trade_no'] = $transactionId;
        OrderService::getInstance()->setUserId($orderInfo['user_id'])
            ->payment($orderInfo['order_sn'], $payInfo);
        return finish(0, '操作成功');
    }
}
