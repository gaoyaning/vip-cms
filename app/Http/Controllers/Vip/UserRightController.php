<?php
namespace  App\Http\Controllers\Vip;

use App\Models\Product;
use App\Models\Recommend;
use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Models\ReceiveRecord;
use App\Services\CaptchaService;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class UserRightController extends Controller
{
    public function allRecommendRightQuery(Request $req) {
        $params = $req->params;
        $params['telco_id'] = array_get($params, 'user.telco_id', 0);
        //$params['level_id'] = array_get($params, 'user.level_id', 0);
        try {
            $rights = Recommend::queryClassifyByLevel($params);
            $navigation = ProductType::navigation();
        } catch (\Exception $e) {
            \Log::error('------ allRecommendRightQuery ------', [$e->getCode(), $e->getMessage()]);
            ResponseService::returnArr([], '系统错误', $e->getCode());
        }
        $values = [
            'navigation' => $navigation,
            'data' => $rights,
        ];
        return ResponseService::returnArr($values); 
    }

    public function userRecommendiValidRightQuery(Request $req) {
        $params = $req->params;
        $params['telco_id'] = array_get($params, 'user.telco_id', 0);
        $params['level_id'] = array_get($params, 'user.level_id', 0);
        try {
            $arr    = ReceiveRecord::getUserReceiveRecords($params);
            $values = Recommend::queryClassifyByLevel($params, $arr);
        } catch (\Exception $e) {
            \Log::error('------ userRecommendRightQuery ------', [$e->getCode(), $e->getMessage()]);
            ResponseService::returnArr([], '系统错误', $e->getCode());
        }
        foreach ($values as $index => $value) {
            $lists = $value['lists'];

            foreach ($lists as $key => $list) {
                if (2 == $list['status']) {
                    unset($lists[$key]);
                }
            }
            $values[$index]['lists'] = $lists;
            //unset($values[$index]['list']);
        }
        return ResponseService::returnArr($values); 
    }

    public function userRecommendRightQuery(Request $req) {
        $params = $req->params;
        $params['telco_id'] = array_get($params, 'user.telco_id', 0);
        $params['level_id'] = array_get($params, 'user.level_id', 0);
        try {
            $arr    = ReceiveRecord::getUserReceiveRecords($params);
            $values = Recommend::queryClassifyByLevel($params, $arr);
        } catch (\Exception $e) {
            \Log::error('------ userRecommendRightQuery ------', [$e->getCode(), $e->getMessage()]);
            ResponseService::returnArr([], '系统错误', $e->getCode());
        }
        foreach ($values as $index => $value) {
            $lists = $value['lists'];
            $new_lists = [
                'using'   => [],
                'used'    => [],
                'expired' => [],
            ];
            foreach ($lists as $list) {
                if (0 == $list['status']) {
                    $new_lists['using'][] = $list;
                } elseif (1 == $list['status']) {
                    $new_lists['used'][] = $list;
                } elseif (2 == $list['status']) {
                    $new_lists['expired'][] = $list;
                }
            }
            $values[$index]['lists'] = $new_lists;
            //unset($values[$index]['list']);
        }
        return ResponseService::returnArr($values); 
    }

    public function levelRecommendRightQuery(Request $req) {
        $params = $req->params;
        $params['telco_id'] = array_get($params, 'user.telco_id', 0);
        $params['level_id'] = array_get($params, 'user.level_id', 0);
        try {
            $rights = Recommend::queryClassifyByLevel($params);
            $navigation = ProductType::navigation();
        } catch (\Exception $e) {
            \Log::error('------ levelRecommendRightQuery ------', [$e->getCode(), $e->getMessage()]);
            ResponseService::returnArr([], '系统错误', $e->getCode());
        }
        $values = [
            'navigation' => $navigation,
            'data' => $rights,
        ];
        return ResponseService::returnArr($values); 
    }

    public function productDetail(Request $req) {
        $params = $req->params;
        try {
            $values = Product::queryProductDetail($params);
        } catch (\Exception $e) {
            \Log::error('------ levelRecommendRightQuery ------', [$e->getCode(), $e->getMessage()]);
            return ResponseService::returnArr([], '系统错误', $e->getCode());
        }
        return ResponseService::returnArr($values); 
    }

    public function getCaptcha(Request $req) {
        $params = $req->params;
        $status = CaptchaService::getCaptcha($params);
        if ($status) {
            return ResponseService::returnArr([], 'status', 4002); 
        } else {
            return ResponseService::returnArr(); 
        }
    }
}
