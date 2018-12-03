<?php

namespace App\Http\Middleware;

use Closure;
use Validator;
use App\Services\ResponseService;

class ParamsCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $uri = $_SERVER["REQUEST_URI"];
        $body = $request->all();
        $params = [];
        if (is_array($body)) {
            $params = $body;
        } else if (is_string) {
            $bodyArr = json_decode($body, true);
            if ($bodyArr) {
                $params = $bodyArr;
            }
        }
        $params['uri'] = $uri;
        if ('/api/admin/v1/image/upload' == $uri) {
            $img_path = storage_path('upload');
            if (!is_dir($img_path)) {
                mkdir($img_path);
            }
            \Log::debug($img_path);
            foreach ($body as $key => $file) {
                if (is_object($file)) {
                    $name = md5($file);
                    $path = $img_path . "/" .$name;
                    file_put_contents($path, file_get_contents($file));
                    $params[$key] = [
                        'object' => $name,
                        'path'   => $path,
                    ];
                }
            }
        } else {
            $params = $this->change($params);
        }
        $params['uri'] = $uri;

        // 验证各个uri传递的值是否合法 ==todo
        if ($res = $this->check($uri, $params)) {
            return ResponseService::response($res);
        }

        $request->params = $params;
        $response =  $next($request);
        
        return $response;
    }

    private function check($uri, $params) {
        $res = [
            'code' => 6000,
            'message'  => '',
        ];
        switch ($uri) {
            case '/api/admin/v1/account/modify':
                $rules = config('paramsrules.system.account.modify');
                $validation = Validator::make($params, $rules);
                if ($validation->fails()) {
                    $res['message']    = '参数错误，请确认';
                    $res['values'] = $validation->errors();
                } else {
                    $res = false;
                }
                break;
            default:
                return false;
        }
        return $res;
    }

    private function humpToLine($str){
        $arr = json_decode($str, true);
        if ($arr) {
            $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
                    return '_'.strtolower($matches[0]);
                    },$str);
            return $str;
        } else {
            return $str;
        }
    }

    private function humpToKey($str){
        $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
                return '_'.strtolower($matches[0]);
                },$str);
        return $str;
    }

    private function change($params) {
        foreach ($params as $key => $value) {
            if ('token' == $key || 'content' == $key) {
                continue;
            }
            unset($params[$key]);
            $new_key = $this->humpToKey($key);
            $params[$new_key] = $this->jsonToArr($this->humpToLine($value));
        }
        return $params;
    }

    private function jsonToArr($value) {
        $new_value = json_decode($value, true);
        if ($new_value) {
            return $new_value;
        }
        return $value;
    }
}
