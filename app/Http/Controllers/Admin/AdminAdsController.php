<?php
namespace  App\Http\Controllers\Admin;

use App\Models\AdType;
use App\Models\AdConfig;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class AdminAdsController extends Controller
{
    public function query(Request $req) {
        $params = $req->params;
        try {
            $values = AdConfig::queryAds($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function add(Request $req) {
        $params = $req->params;
        try {
            $values = AdConfig::addAd($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function type(Request $req) {
        $params = $req->params;
        try {
            $values = AdType::queryAdTypes($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function addType(Request $req) {
        $params = $req->params;
        try {
            AdType::addAdType($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function modify(Request $req) {
        $params = $req->params;
        try {
            AdConfig::modifyAd($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function detail(Request $req) {
        $params = $req->params;
        try {
            $values = AdConfig::adDetail($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function del(Request $req) {
        $params = $req->params;
        try {
            AdConfig::deleteAds($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function online(Request $req) {
        $params = $req->params;
        try {
            AdConfig::updateAdStatus($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }
}
