<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;
use Overtrue\Pinyin\Pinyin;
use think\exception\ValidateException;

class GoodsImageService extends BaseService
{
    protected string $table = 'goods_image';
    protected string $tableUniqueKey = 'image_id';

    // 设置图片
    public function setGallery($goodsId, $goodsVersion, array $imageUrlList): self
    {
        $condition = [];
        $condition['goods_id'] = $goodsId;
        $condition['goods_version'] = $goodsVersion;
        $gData = [];
        foreach ($imageUrlList as $index => $imgUrl) {
            $gData[] = array_merge($condition, [
                'image_id' => static::buildSnowflakeId($index),
                'image_url' => $imgUrl
            ]);
        }
        $this->db()->where($condition)->delete();
        if (count($gData)) $this->db()->insertAll($gData);
        return $this;
    }

    // 获取图片
    public function getImages($goodsId, int $goodsVersion = 1): array
    {
        $condition = [];
        $condition['goods_id'] = $goodsId;
        $condition['goods_version'] = $goodsVersion;
        $field = 'image_url';
        $lists = $this->db()->where($condition)->order('id', 'asc')
            ->select()->toArray();
        $galleryList = [];
        foreach ($lists as $item) $galleryList[] = $item['image_url'];
        return $galleryList;
    }
}