<?php
namespace App\Services;

class RandSalt
{
    private static $strPol = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    public static function randStr($len) {
        $rand_str = '';
        $pol_len = strlen(self::$strPol);
        for ($i = 0; $i < $len; $i++) {
            $pos = mt_rand(0, $pol_len - 1);
            $rand_str = $rand_str . self::$strPol[$pos];
        }
        return $rand_str;
    }

    public static function salt() {
        $str = self::randStr(9);
        return strtoupper(md5($str));
    }
}
