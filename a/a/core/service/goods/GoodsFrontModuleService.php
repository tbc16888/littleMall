<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseFrontModule;

class GoodsFrontModuleService extends BaseFrontModule
{
    protected array $goods = [];

    public function setGoodsInfo(array $goods): self
    {
        $this->goods = $goods;
        return $this;
    }

    public function basic(): self
    {
        $module = [];
        $module['type'] = 'basic';
        $module['data'] = [];
        $field = 'goods_id, goods_name, goods_short_name, shop_price, market_price';
        $field .= ', detail_mobile, goods_image';
        $field = str_replace(' ', '', $field);
        $fieldData = array_intersect_key($this->goods, array_flip(explode(',', $field)));
        $fieldData['is_sale'] = intval($this->goods['sale_status'] == 1);
        $fieldData['activity_price'] = $this->goods['shop_price'];
        $fieldData['sales_volume'] =
            $this->goods['sales_volume'] + $this->goods['virtual_sales_volume'];
        $fieldData['reference_price'] = $this->goods['market_price'];
        $fieldData['final_price'] = $this->goods['shop_price'];
        $fieldData['integral'] = 0;
        if ($this->goods['use_integral_type'] == GoodsIntegralService::INTEGRAL_ONLY) {
            $fieldData['integral'] = $this->goods['use_integral'];
            $fieldData['final_price'] = 0;
            $fieldData['reference_price'] = 0;
        }
        if ($this->goods['use_integral_type'] == GoodsIntegralService::INTEGRAL_AND_CASH) {
            $fieldData['integral'] = $this->goods['use_integral'];
            $fieldData['reference_price'] = 0;
        }

        $module['data']['field'] = $fieldData;

        $this->module[] = $module;
        return $this;
    }

    public function slider(): self
    {
        $module = [];
        $module['type'] = 'slider';
        $module['data']['list'] = [];
        $images = GoodsImageService::getInstance()
            ->getImages($this->goods['goods_id'], $this->goods['goods_version']);
        foreach ($images as $image) {
            $module['data']['list'][] = ['type' => 'image', 'value' => $image];
        }
        $this->module[] = $module;
        return $this;
    }

    // 基础属性
    public function attribute(): self
    {
        $attr = [];
        $attr['type'] = 'attribute';
        $attr['data'] = [];
        $baseAttr = GoodsAttributeService::getInstance()->get($this->goods['goods_id'],
            $this->goods['goods_version'], GoodsAttributeService::BASE);
        $attrList = [];
        $attrName = [];
        foreach ($baseAttr as $item) {
            $attrName[] = $item['attr_name'];
            $attrList[] = ['label' => $item['attr_name'],
                'value' => implode(' ', $item['value'])];
        }
        $attr['data']['field']['text'] = implode(' ', $attrName);
        $attr['data']['list'] = $attrList;
        $attr['data']['show'] = count($baseAttr);
        $this->module[] = $attr;
        return $this;
    }

    // 规格属性
    public function spec(): self
    {

        $attr = [];
        $attr['type'] = 'spec';
        $attr['data'] = [];
        $baseAttr = GoodsAttributeService::getInstance()->get($this->goods['goods_id'],
            $this->goods['goods_version'], GoodsAttributeService::SPEC);
        $attrList = [];
        $attrName = [];
        $images = [];
        foreach ($baseAttr as $key => $item) {
            $attrName[] = $item['attr_name'];
            $attrList[] = ['label' => $item['attr_name'],
                'attr_name' => $item['attr_name'],
                'props' => $item['props']];
            foreach ($item['props'] as $prop) $images[] = $prop['image'];
        }
        $images = array_unique($images);
        array_unshift($attrName, '请选择');
        $attr['data']['list'] = $attrList;
        $attr['data']['field']['text'] = implode(' ', $attrName);
        $attr['data']['field']['images'] = array_filter($images);
        $attr['data']['show'] = count($baseAttr);
        $this->module[] = $attr;
        return $this;
    }

    // sku 列表
    public function sku(): self
    {
        $sku = [];
        $sku['type'] = 'sku';
        $field = 'goods_image, shop_price, stock, goods_sku_text, goods_attr';
        $field .= ', goods_sku_key, goods_sku_id';
        $skuList = GoodsSkuService::getInstance()->db()->field($field)
            ->where('goods_id', $this->goods['goods_id'])
            ->where('goods_version', $this->goods['goods_version'])
            ->where('shop_price', '>', 0)->select()->toArray();
        $sku['data']['list'] = $skuList;
        $this->module[] = $sku;
        return $this;
    }

    // 倒计时
    public function countdown(): self
    {
        $countdown = [];
        $countdown['type'] = 'countdown';
        $countdown['data']['show'] = 1;
        $countdown['data']['field'] = [];
        $countdown['data']['field']['label'] = '疯狂抢购';
        $countdown['data']['field']['start'] = 1800;
        $countdown['data']['style'] = [
            'background' => 'https://img12.360buyimg.com/img/s750x100_jfs/t1/120126/2/5105/50979/5ee98c8fE89e0c5e7/62ae747801d915b8.png',
            'background' => 'https://img12.360buyimg.com/img/s750x100_jfs/t1/119650/20/7754/47352/5ed632eaE8b4d8095/1e2456ef7139c95f.png',
            'indicator' => [
                'color' => '#f71471',
                'background' => '',
            ],
        ];
        $this->module[] = $countdown;
        return $this;
    }

    // banner
    public function banner(): self
    {
        $banner = [];
        $banner['type'] = 'banner';
        $banner['data']['field']['image'] = 'https://img12.360buyimg.com/img/s750x100_jfs/t1/153061/18/15107/99354/6001c03dE08c6dcab/fac73f98de764bc9.png.webp';
        $banner['data']['show'] = 0;
        $this->module[] = $banner;
        return $this;
    }


    public function operation(): self
    {
        $module = [];
        $module['type'] = 'operation';


        // 积分兑换
        $useIntegralType = $this->goods['use_integral_type'];
        if ($useIntegralType == GoodsIntegralService::INTEGRAL_ONLY) {
            $button = [];
            $button['text'] = '立即兑换';
            $button['code'] = 'buy';
            $button['background'] = '#ff6034';
            $button['background2'] = '#ee0a24';
            $module['data']['list'][] = $button;
        } else if ($useIntegralType == GoodsIntegralService::INTEGRAL_AND_CASH) {
            $button = [];
            $button['text'] = '立即购买';
            $button['code'] = 'buy';
            $button['background'] = '#ff6034';
            $button['background2'] = '#ee0a24';
            $module['data']['list'][] = $button;
        } else {
            $button = [];
            $button['text'] = '加入购物车';
            $button['code'] = 'cart';
            $button['background'] = '#ffd01e';
            $button['background2'] = '#ff8917';
            $module['data']['list'][] = $button;

            $button = [];
            $button['text'] = '立即购买';
            $button['code'] = 'buy';
            $button['background'] = '#ff6034';
            $button['background2'] = '#ee0a24';
            $module['data']['list'][] = $button;
        }
//        $button = [];
//        $button['text'] = '即将开始';
//        $button['code'] = '';
//        $button['background'] = '#CCC';
//        $module['data']['list'] = [$button];
        $this->module[] = $module;
        return $this;
    }
}