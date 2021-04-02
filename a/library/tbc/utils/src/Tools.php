<?php
declare(strict_types=1);

namespace tbc\utils;

class Tools
{
    // 获取客户端IPv4地址
    public static function GetClientIPv4(): string
    {
        if (getenv("HTTP_CLIENT_IP")) return getenv("HTTP_CLIENT_IP");
        if (getenv("HTTP_X_FORWARDED_FOR")) return getenv("HTTP_X_FORWARDED_FOR");
        if (getenv("REMOTE_ADDR")) return getenv("REMOTE_ADDR");
        return '0.0.0.0';
    }

    // 简单的 curl 封装
    public static function SimpleCurl(string $url, $data = '', $method = 'post', array $header = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (isset($_SERVER['HTTP_USER_AGENT'])) curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_REFERER, '');
        curl_setopt($curl, CURLOPT_POST, (strtoupper($method) == 'POST' ? 1 : 0));
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    //  获取时间
    public static function getTimeSection($unit = 'd', $date = ''): array
    {
        if ($date === '') $date = date('Y-m-d');

        if (strtolower($unit) === 'd') {
            return [
                date('Y-m-d 00:00:00', strtotime($date)),
                date('Y-m-d 23:59:59', strtotime($date)),
            ];
        } elseif (strtolower($unit) === 'w') {
            $weekDayNumber = date('w', strtotime($date));
            if (0 === $weekDayNumber) $weekDayNumber = 7;
            $start = strtotime($date) - ($weekDayNumber - 1) * 86400;
            return [
                date('Y-m-d 00:00:00', $start),
                date('Y-m-d 23:59:59', $start + 6 * 86400)
            ];
        } else {
            return [
                date('Y-m-01 00:00:00', strtotime($date)),
                date('Y-m-t 23:59:59', strtotime($date))
            ];
        }
    }

    // 异步请求
    public static function AsyncRequest(string $url, $param = [])
    {
        $urlInfo = parse_url($url);
        $host = $urlInfo['host'];
        $path = $urlInfo['path'];
        $port = $urlInfo['port'] ?? 80;
        $query = http_build_query($param);
        $fp = fsockopen($host, $port, $errno, $message, 10);
        stream_set_blocking($fp, true);
        $out = [];
        $out[] = "POST " . $path . " HTTP/1.1\r\n";
        $out[] = "host:" . $host . "\r\n";
        $out[] = "content-length:" . strlen($query) . "\r\n";
        $out[] = "content-type:application/x-www-form-urlencoded\r\n";
        $out[] = "connection:close\r\n\r\n";
        $out[] = $query;
        fwrite($fp, implode('', $out));
        usleep(1000);
        fclose($fp);
    }
}