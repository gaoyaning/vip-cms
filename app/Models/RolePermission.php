<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    public function permission() {
        return $this->belongsTo('App\Models\Permission', 'permission_show_id', 'show')
            ->whereIn('status', [ENABLE, DISABLE]);
    }

    public function allPermission() {
        return $this->hasMany('App\Models\Permission', 'parent_id', 'permission_show_id')
            ->whereIn('status', [ENABLE, DISABLE]);
    }

    public function subMenuPermissions() {
        return $this->hasMany('App\Models\Permission', 'parent_id', 'permission_show_id')
            ->whereIn('status', [ENABLE, DISABLE])
            ->where('menu', 1);
    }

    public static function addOrModifyRolePermissions($role_id, $permissions) {
        $permission_hash = [];
        foreach ($permissions as $father) {
            $id = $father['key'];
            $permission_hash[$id] = $id;
            $children = array_get($father, 'children', []);
            foreach ($children as $child) {
                $id = $child['key'];
                $permission_hash[$id] = $id;
            }
        }
        $role_permissions = self::where('role_id', $role_id)
            ->where('status', ENABLE)
            ->get();
        foreach ($role_permissions as $role_permission) {
            $permission_show = $role_permission->permission_show;
            if (!isset($permission_hash[$permission_show])) {
                $role_permission->status = DISABLE;
                $role_permission->save();
            } else {
                unset($permission_hash[$permission_show]);
            }
        }
        foreach ($permission_hash as $permission_show) {
            $role_permission = self::where('role_id', $role_id)
                ->where('permission_show_id', $permission_show)
                ->first();
            if (!$role_permission) {
                $role_permission = new self();
                $role_permission->role_id = $role_id;
                $role_permission->permission_show_id = $permission_show;
            }
            $role_permission->status = ENABLE;
            $role_permission->save();
        }
        return;
    }
}
