<?php
declare(strict_types=1);

namespace tbc\utils;

class Validate
{

    // 判断中国手机号码格式
    public static function isChinaMobile(string $mobile): bool
    {
        return (boolean)preg_match("/^(13|14|15|16|17|18|19)[0-9]{9}$/", $mobile);
    }

    // 是否邮箱格式
    public static function isEmail($email): bool
    {
        $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if (strpos($email, '@') !== false && strpos($email, '.') !== false) {
            return (preg_match($chars, $email)) ? true : false;
        } else {
            return false;
        }
    }
}