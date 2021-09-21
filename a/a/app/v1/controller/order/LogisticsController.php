<?php
declare(strict_types=1);

namespace app\v1\controller\order;

use app\Request;
use core\base\BaseController;
use core\service\order\OrderService;

class LogisticsController extends BaseController
{
    /**
     * 物流信息
     * @RequestMapping(path="/index", methods="get")
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
//        $lists = OrderExt::getInstance()->getLogisticsList($orderSn);
        $lists = [
//            ['text' => '暂时还没有快递信息', 'time' => date('Y-m-d H:i:s')]
        ];
//        for ($i = 0; $i < 10; $i++) {
//            $lists[] = ['text' => '您的快件代签收（本人），如有疑问请电联小哥【黄典文，电话：150****3538】。疫情期间顺丰每日对网点消毒、小哥每日测温、配戴口罩，感谢您使用顺丰，期待再次为您服务。', 'time' => date('Y-m-d H:i:s')];
//        }
        $lists[] = [
            'text' => '您的订单已签收，签收人（本人签收）。',
            'time' => date('Y-m-d H:i:s')
        ];
        $lists[] = [
            'text' => '订单正在派送中，请保持电话畅通，快递员（仰泳的鱼，电话 13960000001）。',
            'time' => date('Y-m-d H:i:s')
        ];
        $lists[] = [
            'text' => '快件到达某某营业点',
            'time' => date('Y-m-d H:i:s')
        ];

        $lists[] = [
            'text' => '快件在【某某中转场】完成分拣，准备发往【某某中专场】',
            'time' => date('Y-m-d H:i:s')
        ];
        $lists[] = [
            'text' => '快件在【某某营业点】完成分拣，准备发往【某某中转场】',
            'time' => date('Y-m-d H:i:s')
        ];
        $lists[] = [
            'text' => '顺丰快递已收取快件',
            'time' => date('Y-m-d H:i:s')
        ];
        return finish(0, '获取成功', ['list' => $lists, 'info' => [
            ['label' => '承运公司', 'text' => '顺丰快递'],
            ['label' => '联系电话', 'text' => '11183'],
            ['label' => '运单号', 'text' => 'SF999821983', 'copy' => 1]
        ]]);
    }
}