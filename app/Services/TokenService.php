<?php
namespace App\Services;

class TokenService
{
    const DAY_TIME = 60 * 60 * 24;

    // 获取token
    public static function encode($user_info) {
        // config 中获取加密des-key
        $key = env('DES_KEY', '');
        if ('' == $key) {
            return false;
        }

        $user_info['expire_time'] = time() + static::DAY_TIME;
        $data = json_encode($user_info);
        return openssl_encrypt ($data, 'des-ecb', $key);
    }

    // token 解析
    public static function decode($token) {
        // config 中获取加密des-key
        $key = env('DES_KEY', '');
        if ('' == $key) {
            return false;
        }

        $data = openssl_decrypt ($token, 'des-ecb', $key);
        return json_decode($data, true);
    }
}
