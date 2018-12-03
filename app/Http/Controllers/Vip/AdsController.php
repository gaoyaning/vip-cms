<?php
namespace  App\Http\Controllers\Vip;

use App\Models\AdType;
use App\Models\AdConfig;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class AdsController extends Controller
{
    public function query(Request $req) {
        $params = $req->params;
        $params['telco_id'] = array_get($params, 'user.telco_id', 0);
        try {
            $ad_types = AdType::getAdTypes($params);
            $ad_type_ids = [];
            foreach ($ad_types as $ad_type) {
                $ad_type_ids[] = $ad_type->id;
            }
            $values = AdConfig::getUserAds($params, $ad_type_ids);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], '系统错误', $e->getCode());
        }
        return ResponseService::returnArr($values);
    } 

    public function getTypes(Request $req) {
        $params = $req->params;
        try {
            $values = AdType::queryAdTypes($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], '系统错误', $e->getCode());
        }
        return ResponseService::returnArr($values);
    }
}
