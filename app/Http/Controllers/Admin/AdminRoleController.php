<?php
namespace  App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\RolePermission;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class AdminRoleController extends Controller
{
    public function add(Request $req) {
        $params = $req->params;
        try {
            $role = Role::addRole($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        // 添加角色权限
        $permissions = array_get($params, 'permissions', []);
        try {
            RolePermission::addOrModifyRolePermissions($role->id, $permissions);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function delete(Request $req) {
        $params = $req->params;
        try {
            Role::deleteRole($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function start(Request $req) {
        $params = $req->params;
        try {
            Role::startRole($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function info(Request $req) {
        $params = $req->params;
        try {
            $values = Permission::permissions($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function query(Request $req) {
        $params = $req->params;
        try {
            return Role::queryRoles($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
    }

    public function modify(Request $req) {
        $params = $req->params;
        try {
            $role = Role::modifyRole($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        // 添加角色权限
        $permissions = array_get($params, 'permissions', []);
        try {
            RolePermission::addOrModifyRolePermissions($role->id, $permissions);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function detail(Request $req) {
        $params = $req->params;
        try {
            $values = Role::roleDetail($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }
}
