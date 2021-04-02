<?php
// 应用公共文件

if (!function_exists('finish')) {
    function finish(int $code, string $message = '', $data = [], array $ext = [])
    {
        // header('Content-Type: text/json');
        $response = ['code' => $code, 'message' => $message, 'data' => $data];
        $response = array_merge($response, $ext);
        // return json_encode($response, JSON_UNESCAPED_UNICODE);
        return json($response);
    }
}

if (!function_exists('session')) {

    function session($name, $value = '')
    {
        // 初始化
        if (is_array($name)) return \tbc\utils\Session::init($name);
        // 清除
        if (is_null($name)) return \tbc\utils\Session::clear();
        // 判断或获取
        if ('' === $value) return 0 === strpos($name, '?') ? \tbc\utils\Session::has(substr($name, 1)) : \tbc\utils\Session::get($name);
        // 删除
        if (is_null($value)) return \tbc\utils\Session::delete($name);
        // 设置
        return \tbc\utils\Session::set($name, $value);
    }
}