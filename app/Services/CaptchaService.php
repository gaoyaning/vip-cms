<?php
namespace App\Services;

class CaptchaService {
    public static function getCaptcha($params) {
        $mobile  = array_get($params, 'mobile', '');
        $captcha = array_get($params, 'captcha', '');
        if ('' == $mobile) {
            return '手机号码不能为空';
        }
        // 获取验证码
        // 如果失败-抛出异常
        return 667788;
    }
}
