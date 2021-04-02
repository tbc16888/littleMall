<?php
declare(strict_types=1);

namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    // 获取POST请求参数
    public function getPostParams($fieldList = [], callable $callable = null): array
    {
        return $this->getParams($fieldList, 'POST', $callable);
    }

    // 获取GET请求参数
    public function getGetParams($fieldList = [], callable $callable = null): array
    {
        return $this->getParams($fieldList, 'GET', $callable);
    }

    // 获取请求参数
    public function getParams($fieldList = [], $method = 'param', callable $callable = null): array
    {
        if (is_string($fieldList)) $fieldList = explode(',', $fieldList);
        $params = [];
        foreach ($fieldList as $field) {
            $field = trim($field);
            if (!$this->has($field)) continue;
            $params[$field] = call_user_func([$this, $method], $field, '', 'trim');
            if (is_callable($callable)) {
                $params[$field] = $callable($params[$field], $field);
            }
        }
        return $params;
    }
}
