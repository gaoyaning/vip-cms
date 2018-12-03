<?php
namespace App\Services;

use Symfony\Component\HttpFoundation\JsonResponse as Response;

class ResponseService
{
    // 根据arr获取response
    public static function response($arr) {
        $response = new Response($arr);
        $response->original = $arr;
        return $response;
    }

    public static function returnArr($values = [], $message = '操作成功', $code = 200) {
        return [
            'code'    => $code,
            'message' => $message,
            'values'  => $values,
        ];
    }
}
