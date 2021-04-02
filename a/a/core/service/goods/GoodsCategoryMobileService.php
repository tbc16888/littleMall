<?php
declare(strict_types=1);

namespace core\service\goods;

use core\base\BaseService;
use think\exception\ValidateException;

class GoodsCategoryMobileService extends GoodsCategoryService
{

    protected string $table = 'goods_category_mobile';
    protected string $tableUniqueKey = 'cat_id';
}