<?php
namespace App\Http\Middleware;

use Closure;

class LogMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $begin  = START_TIME;
        $uri = $_SERVER["REQUEST_URI"];

        $response =  $next($request);

        $end = microtime(true);
        $consumer = floor(($end-$begin)*1000);

        $response_arr = [];
        if (is_array($response)) {
            $response_arr = $response;
        } else {
            $response_arr = $response->original;
            $response->params = null;
        }

        // 敏感词过滤
        $origin = $request->all();
        if (isset($origin['password'])) {
            $origin['password'] = '******';
        }

        \Log::info('resonse', ['uri' => $uri, 'consumer' => $consumer,'origin'=>$origin, 'response' => $response_arr]);
        // response 下划线转驼峰
        return $this->change($response);
    }

    private function change($response) {
        $body = $response->original;
        if (is_array($body)) {
            $body = json_encode($body);
        }
        $body = $this->convertUnderline($body);
        $body = json_decode($body, true);
        if (isset($body['msg'])) {
            $body['message'] = $body['msg'];
            unset($body['msg']);
        }
        $response->original = $body;
        $response->setData($body);
        return $response;
    }

    private function convertUnderline($str)
    {
        $str = preg_replace_callback('/([_]+([a-z]{1}))/i',function($matches){
                return strtoupper($matches[2]);
            },$str);
        return $str;
    }
}
