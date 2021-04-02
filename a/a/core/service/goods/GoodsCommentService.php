<?php
declare(strict_types=1);
namespace core\service\goods;

use core\base\BaseService;
use think\exception\ValidateException;

class GoodsCommentService extends BaseService
{

    protected string $table = 'goods_comment';
    protected string $tableUniqueKey = 'comment_id';

}