<?php
declare(strict_types=1);

namespace core\base;

class BaseModel extends \think\Model
{
    public static function where($field, $op = null, $condition = null)
    {
        return parent::where($field, $op, $condition)->where('is_delete', 0);
    }
}