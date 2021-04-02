<?php
declare(strict_types=1);

namespace core\service\cart;

use core\constant\goods\GoodsIntegral;

class CartFrontModuleService
{
    public function items(array $goods): array
    {
        $items = [];
        $items['type'] = 'items';
        $items['data']['list'] = [];

        $field = 'goods_id,goods_name,goods_number,goods_sku_id,goods_sku_key';
        $field .= ',shop_price,reason,is_invalid,is_checked,goods_sku_text';
        $field .= ',cart_id,goods_image,is_checked';
        $field = array_flip(explode(',', $field));
        foreach ($goods as $item) {
            $itemData = array_intersect_key($item, $field);
            $itemData['goods_price'] = $item['shop_price'];
            if ($item['use_integral_type'] == GoodsIntegral::INTEGRAL_ONLY) {
                $itemData['integral'] = $item['use_integral'];
                $itemData['goods_price'] = 0;
            } else if ($item['use_integral_type'] == GoodsIntegral::INTEGRAL_AND_CASH) {
                $itemData['integral'] = $item['use_integral'];
//                $itemData['goods_price'] = 199199.01;
            } else {
                $itemData['integral_discount'] = $item['use_integral'];
            }
            $itemData['integral'] = $itemData['integral'] ?? 0;
            $itemData['integral_discount'] = $itemData['integral_discount'] ?? 0;
            $items['data']['list'][] = $itemData;
        }
        return $items;
    }

    //
    public function submit(array $goods): array
    {
        $submit = [];
        $submit['type'] = 'submit';
        $submit['data'] = [];
        $submit['data']['field'] = [];
        $submit['data']['field']['note'] = '';

        $totalGoods = 0;
        $totalPrice = 0;
        $totalChecked = 0;
        foreach ($goods as $item) {
            if (!$item['is_checked']) continue;
            $totalChecked += 1;
            $totalGoods += $item['goods_number'];
            $totalPrice += $item['goods_number'] * $item['goods_price'];
        }
        $isAllChecked = intval($totalChecked == count($goods));
        $background = $totalChecked ? '#1dbd61' : '#CCC';
        $background = $totalChecked ? '#d81e06' : '#CCC';
        $submit['data']['field']['is_all_checked'] = $isAllChecked;
        $submit['data']['field']['is_can_submit'] = intval($totalChecked);
        $submit['data']['field']['total_price'] = number_format($totalPrice, 2);
        $submit['data']['field']['total_goods'] = $totalGoods;
        $submit['data']['style']['background'] = $background;
        $submit['data']['style']['fontSize'] = 40;
        $submit['show'] = count($goods);
        return $submit;
    }

}