<?php
namespace  App\Http\Controllers\Admin;

use App\Models\Telco;
use App\Models\Level;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class AdminTelcoController extends Controller
{
    public function add(Request $req) {
        $params = $req->params;
        try {
            Telco::addTelco($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function info(Request $req) {
        $params = $req->params;
        try {
            $values = Telco::telcoInfo($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function query(Request $req) {
        $params = $req->params;
        try {
            return Telco::queryTelcoes($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
    }

    public function modify(Request $req) {
        $params = $req->params;
        try {
            Telco::modifyTelco($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function delete(Request $req) {
        $params = $req->params;
        try {
            Telco::deleteTelco($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function disable(Request $req) {
        $params = $req->params;
        $params['type'] = 0;
        try {
            Telco::updateTelco($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function enable(Request $req) {
        $params = $req->params;
        $params['type'] = 0;
        try {
            Telco::updateTelco($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function detail(Request $req) {
        $params = $req->params;
        try {
            $values = Telco::telcoDetail($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function loginCreate(Request $req) {
        $params = $this->getParams($req);
        $params['salt'] = RandSalt::salt();
        if ($params['password'] != $params['confirmPwd']) {
            return ResponseService::returnArr([], '密码不一致', 4004);
        }
        $params['password'] = md5($params['password'] . '&' . $params['salt']);
        try {
            return Telco::loginCreate($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
    }

    // 运营商等级维护
    public function addLevel(Request $req) {
        $params = $req->params;
        try {
            Level::addLevel($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function allLevel(Request $req) {
        $params = $req->params;
        try {
            $values = Level::allLevel($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function queryLevels(Request $req) {
        $params = $req->params;
        try {
            return Level::queryLevels($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
    }

    public function modifyLevel(Request $req) {
        $params = $req->params;
        try {
            Level::modifyLevel($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function detailLevel(Request $req) {
        $params = $req->params;
        try {
            $values = Level::levelDetail($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function deleteLevel(Request $req) {
        $params = $req->params;
        try {
            $values = Level::deleteLevel($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }
}
