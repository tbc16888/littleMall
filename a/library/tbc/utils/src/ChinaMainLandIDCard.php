<?php
declare(strict_types=1);

namespace cbt;

class ChinaMainLandIDCard
{
    // 计算身份证校验码，根据国家标准GB 11643-1999
    public static function IDCardVerifyNumber($idCardBase)
    {
        if (strlen($idCardBase) != 17) return false;
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);// 加权因子
        // 效验码对应值
        $checksum = 0;
        $verifyNumberList = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        for ($i = 0; $i < strlen($idCardBase); $i++) $checksum += substr($idCardBase, $i, 1) * $factor[$i];
        $mod = $checksum % 11;
        return $verifyNumberList[$mod];
    }

    // 将15位身份证升级到18位
    public static function IDCard15to18($idCard)
    {
        if (strlen($idCard) != 15) return false;
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        $idCard = (array_search(substr($idCard, 12, 3), array('996', '997', '998', '999')) !== false) ?
            substr($idCard, 0, 6) . '18' . substr($idCard, 6, 9) : substr($idCard, 0, 6) . '19' . substr($idCard, 6, 9);
        $idCard = $idCard . (self::IDCardVerifyNumber($idCard));
        return $idCard;
    }

    // 18位身份证校验码有效性检查
    public static function Test($idCard): bool
    {
        if (strlen($idCard) != 18) return false;
        $idCardBase = substr($idCard, 0, 17);
        return (self::IDCardVerifyNumber($idCardBase) != strtoupper(substr($idCard, 17, 1))) ? false : true;
    }
}