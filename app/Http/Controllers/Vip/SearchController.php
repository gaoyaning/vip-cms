<?php
namespace  App\Http\Controllers\Vip;

use App\Services\Redis;
use App\Models\Product;
use App\Models\HotSearch;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public static $search_key  = 'user_search_product';
    public static $history_key = 'user_history_array';
    public static function search(Request $req) {
        $params = $req->params;
        $word   = array_get($params, 'word', '');
        $userid = array_get($params, 'user.id', 0);
        if (0 == $userid) {
            return ResponseService::returnArr([], '请登录', 7001);
        }
        if (0 != $userid) {
            $search_words = Redis::hget(self::$history_key, $userid);
            $search_words = json_decode($search_words, true);
            if ($search_words && !in_array($word, $search_words)) {
                $search_words[] = $word;
                Redis::hset(self::$history_key, $userid, json_encode($search_words));
            } else if (!$search_words){
                $search_words[] = $word;
                Redis::hset(self::$history_key, $userid, json_encode($search_words));
            }
        }
        $values = [];
        $products = Redis::get(self::$search_key);
        if (!$products) {
            try {
                $products = Product::where('status', ENABLE)
                    ->get();
            } catch (\Exception $e) {
                return ResponseService::returnArr([], '系统错误', 7002);
            }
            Redis::set(self::$search_key, $products);
        }
        if (!is_object($products)) {
            $products = json_decode($products);
        }
        foreach ($products as $product) {
            $match = [];
            $name  = $product->name;
            $pattern = "/$word/";
            $hit = preg_match($pattern, $name, $match);
            if ($hit) {
                $urls = json_decode($product->pic_url, true);
                $values[] = [
                    'id'   => $product->id,
                    'name' => $product->name,
                    'subTitle' => $product->sub_title,
                    'urls' => $urls,
                ];
            }
        }
        return ResponseService::returnArr($values);
    }

    public static function history(Request $req) {
        $params = $req->params;
        $userid = array_get($params, 'user.id', 0);
        $values = [];
        if (0 != $userid) {
            $search_words = Redis::hget(self::$history_key, $userid);
            if ($search_words) {
                $values = json_decode($search_words, true);
            }
        }
        return ResponseService::returnArr($values);
    }

    public static function hotSearch(Request $req) {
        $values = [];
        try {
            $searches = HotSearch::where('status', ENABLE)
                ->get();
            foreach ($searches as $search) {
                $values[] = $search->word;
            }
        } catch (\Exception $e) {
            return ResponseService::returnArr([], '系统错误', 7002);
        }
        return ResponseService::returnArr($values);
    }
}
