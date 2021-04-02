<?php
declare(strict_types=1);

namespace app\admin\subscribe;

use think\Event;
use core\service\goods\GoodsService;
use core\service\goods\GoodsSkuService;

class GoodsSubscribe
{
    public function subscribe(Event $event)
    {
        $event->listen('afterGoodsUpdate', [$this, 'afterGoodsUpdate']);
    }

    public function afterGoodsUpdate($data): ?bool
    {
        $skuService = GoodsSkuService::getInstance();
        $condition = [];
        $condition['goods_id'] = $data['goods_id'];
        $condition['goods_version'] = $data['goods_version'];
        $skuTotal = $skuService->dbQuery($condition)->count();
        if (0 === $skuTotal) return true;

        $minPrice = $skuService->dbQuery($condition)->min('shop_price');
        $stockTotal = $skuService->dbQuery($condition)->sum('stock');
        $rData = [];
        $rData['sku_total'] = $skuTotal;
        $rData['shop_price'] = $minPrice;
        $rData['stock_total'] = $stockTotal;
        GoodsService::getInstance()->db()->where($condition)->limit(1)
            ->update($rData);
        return true;
    }
}