<?php
namespace App\Models;

use App\Models\UserRole;
use App\Services\ResponseService;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function userRoles() {
        return $this->hasMany('\App\Models\UserRole')
            ->where('status', ENABLE);
    }

    public static function getSystemUserByID($params) {
        $user = self::find($params['user_id']);
        $roles_info = [];
        $roles = $user->userRoles;
        foreach ($roles as $user_role) {
            if (!$user_role->role) {
                continue;
            }
            $roles_info[] = [
                'id'   => $user_role->role->id,
                'name' => $user_role->role->name,
            ];
        }
        $values = [
            'id'       => $user->id,
            'name'     => $user->real_name,
            'roles'    => $roles_info,
            'mobile'   => $user->mobile,
            'status'   => $user->status,
            'username' => $user->name,
            'desc'     => $user->desc,
        ];
        return $values;
    }

    public static function querySystemUser($params) {
        $page  = array_get($params, 'page', 1);
        $users = self::whereIn('type', [1, 2]);
        $status = array_get($params, 'status', 2);
        0 == $status && $users = $users->where('status', DISABLE);
        1 == $status && $users = $users->where('status', ENABLE);
        2 == $status && $users = $users->whereIn('status', [DISABLE, ENABLE]);

        $name = array_get($params, 'name', '');
        '' != $name && $users = $users->where('real_name', $name);
        $username = array_get($params, 'username', '');
        '' != $username && $users = $users->where('name', $username);

        // 获取总条目
        
        // 获取分页信息
        $page_size = array_get($params, 'page_size', 20);
        $user_list = $users->paginate($page_size, ['*'], 'page', $page)->toArray();

        $list = [];
        foreach ($user_list['data'] as $user_info) {
            $roles = UserRole::getUserRolesByUserID($user_info['id']);
            $role_names = '';
            foreach ($roles as $user_role) {
                if ($user_role->role) {
                    $role_names .= $user_role->role->name;
                }
            }
            $list[] = [
                'id'       => $user_info['id'],
                'name'     => $user_info['real_name'],
                'role'     => $role_names,
                'mobile'   => $user_info['mobile'],
                'status'   => $user_info['status'],
                'username' => $user_info['name'],
            ];
        }
        $values = [
            'list'  => $list,
            'total' => $user_list['total'],
        ];
        return $values;
    }

    public static function updateSystemUserStatus($params) {
        $type     = array_get($params, 'type');
        $user_ids = array_get($params, 'user_ids', []);
        self::whereIn('id', $user_ids)
            ->update(['status' => $type]);
        return;
    }

    public static function modifySystemUser($params) {
        $user = User::find($params['user_id']);
        if (isset($params['username']) && '' != $params['username']) {
            $user->name = $params['username'];
        }

        if (isset($params['mobile']) && '' != $params['mobile']) {
            $user->mobile = $params['mobile'];
        }

        if (isset($params['role_desc'])) {
            $user->desc = $params['role_desc'];
        }

        if (isset($params['name']) && '' != $params['name']) {
            $user->real_name = $params['name'];
        }

        if (isset($params['status']) && '' != $params['status']) {
            $user->status = $params['status'];
        }
        $user->save();
        return $user->userRoles;
    }

    public static function resetSystemUserPwd($params) {
        $users = self::whereIn('id', $params['user_ids'])
            ->get();
        $password = array_get($params, 'password', 'Vip@123');
        foreach ($users as $user) {
            $salt = $user->salt;
            $new_pwd = md5($password . '&' . $salt);
            $user->password = $new_pwd;
            $user->save();
        }
    }

    public static function addSystemUser($params) {
        $user = self::where('name', array_get($params, 'name'))
            ->where('status', '>', ERASE)
            ->first();
        if ($user && $user->status > ERASE) {
            return ResponseService::returnArr([], '用户名已存在，请确认', 4000);
        }
        $user = self::where('mobile', array_get($params, 'mobile'))
            ->where('status', '>', ERASE)
            ->first();
        if ($user && $user->status > ERASE) {
            return ResponseService::returnArr([], '用户手机号已存在，请确认', 4001);
        }
        try {
            $user = new self();
            $user->name      = array_get($params, 'username');
            $user->salt      = array_get($params, 'salt');
            $user->mobile    = array_get($params, 'mobile');
            $user->password  = array_get($params, 'password');
            $user->desc      = array_get($params, 'role_desc', '');
            $user->status    = array_get($params, 'status');
            $user->real_name = array_get($params, 'name', '');
            $user->save();
        } catch (Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), 4002);
        }
        // 用户角色save
        UserRole::saveUserRoles($user->id, array_get($params, 'role_ids'));
        // 发送密码到用户手机 ==todo
        return ResponseService::returnArr();
    }
}
