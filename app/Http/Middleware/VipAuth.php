<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\PartnerUser;
use App\Services\TokenService;
use App\Services\ResponseService;

class VipAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $params = $request->params;
        $token = $params['token'];
        $user_info = TokenService::decode($token);
        if (!$user_info) {
            $res = ResponseService::returnArr([], 'token解析失败，请重新登录', 3000);
            return ResponseService::response($res);
        }
        // token是否失效
        $now = time();
        if ($now > $user_info['expire_time']) {
            $res = ResponseService::returnArr([], 'token失效，请重新登录', 3001);
            return ResponseService::response($res);
        }

        $user = PartnerUser::find($user_info['id']);
        if (0 == $user->status) {
            $res = ResponseService::returnArr([], '已退出登录，请登录', 3002);
            return ResponseService::response($res);
        }

        $params['user']  = $user_info;
        $request->params   = $params;
        $response =  $next($request);
        return $response;
    }
}
