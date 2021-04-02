<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;
use Overtrue\Pinyin\Pinyin;
use think\exception\ValidateException;

class GoodsBrandService extends BaseService
{
    protected string $table = 'goods_brand';
    protected string $tableUniqueKey = 'brand_id';

    protected function onBeforeExecute()
    {
        $info = $this->dbQuery()->where('brand_name', $this->formData['brand_name'])
            ->find();
        if (null !== $info && $this->tableUniqueId != $info['brand_id']) {
            throw new ValidateException('品牌名称已存在');
        }
        $brandName = $this->formData['brand_name'] ?? '';
        $this->formData['first_letter'] = $this->formData['first_letter'] ?? '';
        if (!$this->formData['first_letter']) {
            $this->formData['first_letter'] = static::getFirstLetter($brandName);
        }
    }

    public static function getFirstLetter($brandName): string
    {
        if (preg_match('/^[0-9a-zA-Z]/', $brandName)) {
            $firstLetter = $brandName[0];
        } else {
            $pinyin = new Pinyin();
            $result = $pinyin->convert($brandName);
            $letter = implode('', $result);
            $firstLetter = preg_match('/^[0-9a-zA-Z]/', $letter) ? $letter[0] : '#';
        }
        return strtoupper($firstLetter);
    }
}