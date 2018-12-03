<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    public function rolePermisson() {
        return $this->hasMany('App\Models\RolePermission', 'role_id', 'role_id')
            ->where('status', ENABLE);
    }

    public function role() {
        return $this->belongsTo('App\Models\Role', 'role_id', 'id')
            ->where('status', ENABLE);
    }

    public static function getUserRolesByUserID($user_id) {
        return self::where('user_id', $user_id)
            ->where('status', ENABLE)
            ->get();
    }

    public static function saveUserRoles($user_id, $role_ids) {
        foreach ($role_ids as $role_id) {
            $user_role = new self();
            $user_role->user_id = $user_id;
            $user_role->role_id = $role_id;
            $user_role->status  = ENABLE;
            $user_role->save();
        }
        return;
    }

    public static function getUserPermission($user_id) {
        $user_permission_objs = self::where('user_id', $user_id)
            ->where('status', ENABLE)
            ->get();
        $user_permissions = [];
        foreach ($user_permission_objs as $obj) {
            $role_permissons = $obj->rolePermisson;
            $role = $obj->role;
            $user_permissions['role'][] = $role->name;
            foreach ($role_permissons as $role_permisson) {
                $user_permissions['visit'][] = $role_permisson->permission_show_id;
                $sub_lists = $role_permisson->subMenuPermissions;
                foreach ($sub_lists as $list) {
                    $user_permissions['visit'][] = $list->show;
                }
            }
        }
        return $user_permissions;
    }

    public static function getRouteUri($user_id) {
        $user_permission_objs = self::where('user_id', $user_id)
            ->where('status', ENABLE)
            ->get();
        $uris = [];
        foreach ($user_permission_objs as $obj) {
            $role_permissons = $obj->rolePermisson;
            foreach ($role_permissons as $role_permisson) {
                $permissions = $role_permisson->allPermission;
                foreach ($permissions as $permission) {
                    '' != $permission->uri_path && $uris[] = $permission->uri_path;
                }
            }
        }
        return $uris;
    }

    public static function modifyUserRoles($user_id, $role_ids, $user_roles) {
        $new_ids = [];
        foreach ($role_ids as $id) {
            $new_ids[$id] = $id;
        }

        foreach ($user_roles as $user_role) {
            $role_id = $user_role->role_id;
            if (!isset($new_ids[$role_id])) {
                $user_role->status = DISABLE;
                $user_role->save();
            } else {
                unset($new_ids[$role_id]);
            }
        }

        foreach ($new_ids as $role_id) {
            $user_role = self::where('user_id', $user_id)
                ->where('role_id', $role_id)
                ->first();
            if (!$user_role) {
                $user_role = new self();
                $user_role->user_id = $user_id;
                $user_role->role_id = $role_id;
            }
            $user_role->status = ENABLE;
            $user_role->save();
        }
    }
}
