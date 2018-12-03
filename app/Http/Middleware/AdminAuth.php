<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserRole;
use App\Services\TokenService;
use App\Services\ResponseService;
use App\Services\PermissionService;

class AdminAuth
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
            $res = [
                'code' => 3000,
                'message'  => 'token解析失败，请重新登录',
                'values' => [],
            ];
            return ResponseService::response($res);
        }
        // token是否失效
        $now = time();
        if ($now > $user_info['expire_time']) {
            $res = [
                'code' => 3001,
                'message'  => 'token失效，请重新登录',
                'values' => [],
            ];
            return ResponseService::response($res);
        }

        $params['oauth_id']   = array_get($user_info, 'id');
        $params['oauth_tel']  = array_get($user_info, 'mobile');
        $params['oauth_name'] = array_get($user_info, 'username');
        $request->params   = $params;

        $auth_arr = [
            '/api/admin/v1/system/menus',
            '/api/admin/v1/user/info',
            '/api/admin/v1/image/upload',
            '/api/admin/v1/user/changepwd',
        ];

        if (in_array(array_get($params, 'uri', ''), $auth_arr)) {
            return $next($request);
        }

        $reject = PermissionService::reject(array_get($user_info, 'id', 0), $params['uri']);
        if ($reject) {
            return $reject; 
        }
        /*
        $user_uris = UserRole::getRouteUri(array_get($user_info, 'id', 0));
        if (!in_array(array_get($params, 'uri', ''), $user_uris)) {
            $res = [
                'code' => 3002,
                'message'  => '用户无权限访问该链接',
                'values' => [],
            ];
            return ResponseService::response($res);
        }*/

        $response =  $next($request);
        return $response;
    }
}
