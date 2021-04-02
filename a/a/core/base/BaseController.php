<?php
declare(strict_types=1);

namespace core\base;

class BaseController extends \app\BaseController
{

    // 获取分页-页数
    public function page(string $key = 'page'): int
    {
        return intval($this->request->get($key, 1));
    }

    // 获取分页-条数
    public function size(string $key = 'size', int $size = 10): int
    {
        return intval($this->request->get($key, $size));
    }
}