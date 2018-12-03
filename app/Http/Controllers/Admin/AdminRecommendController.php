<?php
namespace  App\Http\Controllers\Admin;

use App\Models\Right;
use App\Models\Telco;
use App\Models\Product;
use App\Models\Recommend;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class AdminRecommendController extends Controller
{
    public function add(Request $req) {
        $params = $req->params;
        try {
            Recommend::addRecommend($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function query(Request $req) {
        $params = $req->params;
        try {
            // 运营商查询
            $telco_id = Telco::queryByName($params);
            if (0 != $telco_id) {
                $params['telco_id'] = $telco_id;
            }
            // 产品ID查询
            $product_id = Product::queryByName($params);
            if (0 != $product_id) {
                $params['product_id'] = $product_id;
            }
            $values = Recommend::queryRecommends($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function delete(Request $req) {
        $params = $req->params;
        try {
            Recommend::deleteRecommend($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function all(Request $req) {
        $params = $req->params;
        try {
            return Right::rightsInfo($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
    }

    public function modify(Request $req) {
        $params = $req->params;
        try {
            Recommend::modifyRecommend($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function update(Request $req) {
        $params = $req->params;
        try {
            $values = Recommend::updateRecommend($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function detail(Request $req) {
        $params = $req->params;
        try {
            $values = Recommend::recommendDetail($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function top(Request $req) {
        $params = $req->params;
        try {
            Recommend::topRecommend($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function order(Request $req) {
        $params = $req->params;
        try {
            Recommend::orderRecommend($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }
}
