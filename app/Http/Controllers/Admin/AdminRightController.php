<?php
namespace  App\Http\Controllers\Admin;

use App\Models\Right;
use App\Models\Telco;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class AdminRightController extends Controller
{
    public function add(Request $req) {
        $params = $req->params;
        try {
            Right::addRight($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function query(Request $req) {
        $params = $req->params;
        try {
            // 根据运营商商命获取ID
            $telco_name = array_get($params, 'operator', '');
            if ('' != $telco_name) {
                $telco = Telco::where('name', $telco_name)
                    ->first();
                if ($telco) {
                    $params['telco_id'] = $telco->id;
                }
            }
            // 根据产品名称获取ID
            $poduct_name = array_get($params, 'product_name', '');
            if ('' != $poduct_name) {
                $product = Product::where('name', $poduct_name)
                    ->first();
                if ($product) {
                    $params['product_id'] = $product->id;
                }
            }
            return Right::queryRights($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function info(Request $req) {
        $params = $req->params;
        try {
            return Right::rightsInfo($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
    }

    public function delete(Request $req) {
        $params = $req->params;
        try {
            Right::deleteRight($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function online(Request $req) {
        $params = $req->params;
        $params['type'] = 1;
        try {
            Right::updateRightStatus($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function offline(Request $req) {
        $params = $req->params;
        $params['type'] = 0;
        try {
            Right::updateRightStatus($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function modify(Request $req) {
        $params = $req->params;
        try {
            Right::modifyRight($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function update(Request $req) {
        $params = $req->params;
        try {
            $values = Right::updateRightStatus($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function detail(Request $req) {
        $params = $req->params;
        try {
            $values = Right::rightDetail($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function all(Request $req) {
        $params = $req->params;
        try {
            $values = Product::allRights();
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }
}
