<?php
namespace  App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Permission;
use App\Services\RandSalt;
use Illuminate\Http\Request;
use App\Services\TokenService;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class AdminUserController extends Controller
{
    public function login(Request $req) {
        $params = $this->getParams($req);
        $user = User::where('name', $params['username'])
            ->where('status', 1)
            ->first();
        if (!$user) {
            // 用户不存在
            return ResponseService::returnArr([], '用户不存在', 2001);
        }
        $check_sign = md5($params['password'] . '&' . $user->salt);
        if ($check_sign != $user->password) {
            // 密码错误
            return ResponseService::returnArr([], '密码错误', 2002);
        }
        // 生成token
        $user_info = [
            'id' => $user->id,
            'username' => $user->name,
            'mobile'   => $user->mobile,
            // 'permissions' => UserRole::getUserPermission($user->id),
        ];
        $token = TokenService::encode($user_info);
        if (!$token) {
            return ResponseService::returnArr([], '登陆失败，请重试', 2003);
        }
        return ResponseService::returnArr(['token' => $token]);
    }

    public function userInfo(Request $req) {
        $params = $this->getParams($req);
        $token = $params['token'];
        $user_info = TokenService::decode($token);
        if (empty($user_info)) {
            return ResponseService::returnArr([], '获取用户信息失败', 2004);
        }
        unset($user_info['mobile']);
        unset($user_info['expire_time']);
        // 获取用户权限信息
        $user_info['permissions'] = UserRole::getUserPermission($user_info['id']);
        return ResponseService::returnArr($user_info);
    }

    public function menus(Request $req) {
        // 获取用户权限ID
        $params = $this->getParams($req);
        $user_id = array_get($params, 'oauth_id', 0);
        $permission_infos = UserRole::getUserPermission($user_id);
        $visit_ids = array_get($permission_infos, 'visit', []);
        // 获取系统菜单信息
        $menus = Permission::getUserMenus();
        foreach ($menus as $k => $menu) {
            if (!in_array($menu['id'], $visit_ids)) {
                $menus[$k]['mpid'] = '-1';
            }
        }
        return ResponseService::returnArr(['menus' => $menus]);
    }

    public function add(Request $req) {
        $params = $this->getParams($req);
        $params['salt'] = RandSalt::salt();
        $params['password'] = md5($params['password'] . '&' . $params['salt']);
        return User::addSystemUser($params);
    }

    public function query(Request $req) {
        $params = $this->getParams($req);
        try {
            $values = User::querySystemUser($params); 
        } catch (Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function detail(Request $req) {
        $params = $this->getParams($req);
        try {
            $values = User::getSystemUserByID($params); 
        } catch (Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr($values);
    }

    public function update(Request $req) {
        $params = $this->getParams($req);
        try {
            User::updateSystemUserStatus($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function modify(Request $req) {
        $params = $this->getParams($req);
        try {
            $user_roles = User::modifySystemUser($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        try {
            UserRole::modifyUserRoles($params['user_id'], $params['role_ids'], $user_roles);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    public function changepwd(Request $req) {
        $params = $req->params;
        $user = User::find($params['oauth_id']);
        if (!$user) {
            // 用户不存在
            return ResponseService::returnArr([], '用户不存在', 2001);
        }
        $check_sign = md5($params['old_pwd'] . '&' . $user->salt);
        if ($check_sign != $user->password) {
            // 密码错误
            return ResponseService::returnArr([], '旧密码错误', 2002);
        }
        $new_sign = md5($params['new_pwd'] . '&' . $user->salt);
        $user->password = $new_sign;
        try {
            $user->save();
        } catch (\Exception $e) {
            return ResponseService::returnArr([], '新密码保存失败', 2003);
        }

        // 生成token
        $user_info = [
            'id' => $user->id,
            'username' => $user->name,
            'mobile'   => $user->mobile,
        ];
        $token = TokenService::encode($user_info);
        if (!$token) {
            return ResponseService::returnArr([], '获取用户token失败', 2004);
        }
        return ResponseService::returnArr(['token' => $token]);
    }

    public function resetPwd(Request $req) {
        $params = $this->getParams($req);
        try {
            User::resetSystemUserPwd($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), $e->getCode());
        }
        return ResponseService::returnArr();
    }

    private function getParams($req) {
        $params = $req->params;
        return $params;
    }
}
