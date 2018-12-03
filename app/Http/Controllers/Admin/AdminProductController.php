<?php
namespace  App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class AdminProductController extends Controller
{
    // 商品分类
    public function addCategory(Request $req) {
        $params = $req->params;
        try {
            $values = ProductType::addCategory($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function topCategory(Request $req) {
        $params = $req->params;
        try {
            $values = ProductType::topCategory($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function queryCategory(Request $req) {
        $params = $req->params;
        try {
            $values = ProductType::queryCategory($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function updateCategory(Request $req) {
        $params = $req->params;
        try {
            $values = ProductType::updateCategory($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function modifyCategory(Request $req) {
        $params = $req->params;
        try {
            $values = ProductType::modifyCategory($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function categoryDetail(Request $req) {
        $params = $req->params;
        try {
            $values = ProductType::categoryDetail($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function ascentCategory(Request $req) {
        $params = $req->params;
        try {
            $values = ProductType::ascentCategory($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function descentCategory(Request $req) {
        $params = $req->params;
        try {
            $values = ProductType::descentCategory($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    // 商品信息
    public function addInfo(Request $req) {
        $params = $req->params;
        try {
            $values = Product::addInfo($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function allInfo(Request $req) {
        $params = $req->params;
        try {
            $values = Product::allInfo($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function queryInfo(Request $req) {
        $params = $req->params;
        try {
            $values = Product::queryInfo($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function deleteInfo(Request $req) {
        $params = $req->params;
        try {
            $values = Product::deleteInfo($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function onlineInfo(Request $req) {
        $req->params['type'] = ENABLE;
        return $this->updateInfo($req->params);
    }

    public function offlineInfo(Request $req) {
        $req->params['type'] = DISABLE;
        return $this->updateInfo($req->params);
    }

    public function updateInfo($params) {
        try {
            $values = Product::updateInfo($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function modifyInfo(Request $req) {
        $params = $req->params;
        try {
            $values = Product::modifyInfo($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function infoDetail(Request $req) {
        $params = $req->params;
        try {
            $values = Product::infoDetail($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }
}
