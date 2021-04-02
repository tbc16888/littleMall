<?php
declare(strict_types=1);

namespace core\service\settlement;

use core\base\BaseService;
use core\constant\order\OrderStatus;
use core\constant\goods\GoodsConstant;
use core\exception\BusinessException;
use core\constant\goods\GoodsIntegral;
use core\service\coupon\UserCouponService;
use core\service\goods\GoodsIntegralService;
use core\service\goods\GoodsSkuService;
use core\service\order\OrderService;
use core\service\order\OrderGoodsService;
use core\service\user\UserAddressService;

class SettlementService extends BaseService
{
    protected $settlementGoodsList = [];
    protected $settlementGoodsAmountList = [];

    // 设置结算的商品
    public function setSettlementGoods(array $goodsList): self
    {
        $this->settlementGoodsList = $goodsList;
        return $this;
    }

    // 计算各个商品的金额
    public function calcGoodsTotalPriceOfEach(): self
    {
        $amountList = [];
        foreach ($this->settlementGoodsList as $goods) {

            if (!isset($amountList[$goods['goods_id']])) {
                $amountList[$goods['goods_id']] = 0;
            }

            // 积分兑换的不计入商品总额
            if ($goods['use_integral_type'] == GoodsIntegral::INTEGRAL_ONLY) {
                $amountList[$goods['goods_id']] = 0;
                continue;
            }
            $amountList[$goods['goods_id']] +=
                $goods['shop_price'] * $goods['goods_number'];
        }
        $this->settlementGoodsAmountList = $amountList;
        return $this;
    }

    // 获取商品金额
    public function getGoodsTotalPriceOfEach(): array
    {
        return $this->settlementGoodsAmountList;
    }

    public function setFormData(array $form = []): self
    {
        $this->formData = $form;
        $this->formData['user_coupon_id'] = $this->formData['user_coupon_id'] ?? '';
        $this->formData['user_id'] = $this->userId;
        $this->formData['shipping_type'] = $this->formData['shipping_type'] ?? 1;
        $this->formData['address_id'] = $this->formData['address_id'] ?? '';
        $this->formData['buyer_remark'] = $this->formData['buyer_remark'] ?? '';
        $this->formData['integral'] = intval($this->formData['integral'] ?? 0);
        return $this;
    }

    public function handleFormData(): self
    {
        if (!$this->userId) throw new BusinessException('用户ID错误');

        $this->calcGoodsTotalPriceOfEach();

        defined('PAY_EFFECTIVE_TIME') or define('PAY_EFFECTIVE_TIME', 60 * 20);

        $this->formData['order_id'] = static::buildSnowflakeId();
        $this->formData['order_sn'] = $this->formData['order_id'];
        $this->formData['order_time'] = date('Y-m-d H:i:s');
        $this->formData['order_status'] = OrderStatus::WAIT_PAYMENT;
        $this->formData['user_id'] = $this->userId;
        $this->formData['goods_amount'] = array_sum($this->settlementGoodsAmountList);
        $this->formData['order_amount'] = $this->formData['goods_amount'];
        $this->formData['total_discount_amount'] = 0;
        $this->formData['pay_deadline_time'] = date('Y-m-d H:i:s', time() + PAY_EFFECTIVE_TIME);

        $this->checkGoodsSaleStatusAndStock();
        $this->checkAndHandleAddress();
        $this->checkAndHandleUserCoupon();
        $this->checkAndHandleIntegral();

        if ($this->formData['order_amount'] <= 0) {
            $this->formData['order_status'] = OrderStatus::PAYMENT;
            $this->formData['order_amount'] = 0;
        }
        return $this;
    }

    // 提交
    public function submit(): self
    {
        // 订单商品
        $oderGoodsForm = [];
        $orderStockSql = [];
//        print_r($this->settlementGoodsList);
        foreach ($this->settlementGoodsList as $key => $goods) {
            $rData = [];
            $rData['order_goods_id'] = static::buildSnowflakeId($key);
            $rData['goods_id'] = $goods['goods_id'];
            $rData['order_sn'] = $this->formData['order_sn'];
            $rData['goods_sku_id'] = $goods['goods_sku_id'];
            $rData['goods_version'] = $goods['goods_version'];
            $rData['goods_number'] = $goods['goods_number'];
            $rData['goods_price'] = $goods['shop_price'];
            $rData['activity_price'] = $goods['activity_price'];
            $rData['discount_price'] = $goods['shop_price'] - $goods['activity_price'];
            $rData['integral'] = $goods['settlement_integral'] ?? 0;
            $rData['create_time'] = $this->formData['order_time'];
            $rData['goods_amount'] = $goods['shop_price'] * $goods['goods_number'];
            $oderGoodsForm[] = $rData;

            $orderStockSql[] = $this->db('goods_sku')
                ->where('goods_id', $goods['goods_id'])
                ->where('goods_sku_key', $goods['goods_sku_key'])
                ->whereRaw('stock >= ' . $goods['goods_number'])
                ->exp('stock', 'stock - ' . $goods['goods_number'])
                ->limit(1)->fetchSql(true)->update();
        }


        OrderService::getInstance()->db()->insert($this->formData);
        OrderGoodsService::getInstance()->db()->insertAll($oderGoodsForm);
        foreach ($orderStockSql as $sql) {
            if (!\think\facade\Db::execute($sql)) {
                throw new BusinessException('提交失败[1001]');
            }
        }

        // 更改优惠券成已使用
//        if ($this->formData['user_coupon_id']) {
//            UserCoupon::getInstance()->setCouponStatus($this->formData['user_coupon_id'],
//                UserCoupon::USED, $this->formData['order_sn']);
//        }
//        $this->orderSn = $this->formData['order_sn'];
        return $this;
    }


    // 校验商品库存和销售状态
    protected function checkGoodsSaleStatusAndStock(): self
    {
        foreach ($this->settlementGoodsList as $goods) {
            $goodsName = [];
            $goodsName[] = $goods['goods_name'];
            if ($goods['goods_sku_text']) $goodsName[] = $goods['goods_sku_text'];
            $goodsName = implode(' ', $goodsName);
            if ($goods['sale_status'] !== GoodsConstant::START_SALE) {
                throw new BusinessException($goodsName . '已下架');
            }
            if ($goods['stock'] < $goods['goods_number']) {
                throw new BusinessException($goodsName . '库存不足');
            }
        }
        return $this;
    }

    // 校验收获地址
    protected function checkAndHandleAddress(): self
    {
        // 非实物商品无需地址
        if (count($this->settlementGoodsList) === 1) {
            if ($this->settlementGoodsList[0]['goods_type'] != 1) return $this;
        }
        if ($this->formData['shipping_type'] == 1) {
            if (!($addressId = $this->formData['address_id'])) {
                throw new BusinessException('请选择收货地址');
            }
            $addressInfo = UserAddressService::getInstance()->getInfo($addressId);
            if (null === $addressInfo) throw new BusinessException('地址不存在');
            $addressField = ['contact', 'contact_number', 'gender', 'province',
                'city', 'district', 'street', 'address', 'house_number',
                'longitude', 'latitude'];
            $orderAddress = array_intersect_key((array)$addressInfo, array_flip($addressField));
            $this->formData['address_id'] = $addressId;
            $this->formData = array_merge($this->formData, $orderAddress);
        }
        return $this;
    }

    // 校验优惠券
    protected function checkAndHandleUserCoupon(): self
    {
        if ($this->formData['user_coupon_id']) {
            $couponInfo = UserCouponService::getInstance()->getList(['uc.user_coupon_id' => $this->formData['user_coupon_id']], 1, 1);
            UserCouponService::getInstance()->isUsable($couponInfo, $this->settlementGoodsAmountList);
            $this->formData['coupon_amount'] = $couponInfo['discount'];
            $this->formData['total_discount_amount'] += $couponInfo['discount'];
            $this->formData['order_amount'] -= $couponInfo['discount'];
        }
        return $this;
    }

    // 计算积分优惠
    protected function checkAndHandleIntegral(): self
    {
        // 统计汇总
        $discountIntegral = $this->formData['integral'];
        $integralSummary = GoodsIntegralService::getInstance()->getGoodsListIntegralInfo($this->settlementGoodsList);
        $mustBeUseIntegral = array_sum($integralSummary) - $integralSummary[GoodsIntegralService::INTEGRAL_OR_CASH];
        $this->formData['integral'] += $mustBeUseIntegral;
        $exchangeCash = $discountIntegral / 100;
        $this->formData['total_discount_amount'] += $exchangeCash;
        $this->formData['integral_amount'] = $exchangeCash;
        $this->formData['order_amount'] -= $exchangeCash;
        foreach ($this->settlementGoodsList as &$goods) {

            if (!isset($goods['use_integral_type'])) continue;

            // 只允许积分兑换
            if ($goods['use_integral_type'] == GoodsIntegralService::INTEGRAL_ONLY) {
                /*$this->formData['total_discount_amount'] += $this->settlementGoodsAmountList[$goods['goods_id']];
                $this->formData['order_amount'] -= $this->settlementGoodsAmountList[$goods['goods_id']];*/
                $goods['shop_price'] = 0;
                $goods['settlement_integral'] = $goods['use_integral'];
                $goods['discount_price'] = $goods['activity_price'];
            }

            // 积分或现金
            if ($goods['use_integral_type'] == GoodsIntegralService::INTEGRAL_OR_CASH) {
                $goods['settlement_integral'] = ($goods['use_integral'] * $goods['goods_number']) / $integralSummary[GoodsIntegralService::INTEGRAL_OR_CASH] * $discountIntegral;
            }

            // 现金加积分
            if ($goods['use_integral_type'] == GoodsIntegralService::INTEGRAL_AND_CASH) {
                $goods['settlement_integral'] = ($goods['use_integral'] * $goods['goods_number']);
            }
        }

        return $this;
    }
}