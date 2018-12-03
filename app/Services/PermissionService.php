<?php
namespace App\Services;

use App\Models\UserRole;
use App\Models\RolePermission;
use App\Services\ResponseService;

class PermissionService {
    public static function reject($user_id, $uri) {
        $user_roles = UserRole::where('user_id', $user_id)
            ->where('status', ENABLE)
            ->get();
        $role_ids = [];
        foreach ($user_roles as $user_role) {
            $role_ids[] = $user_role->role_id;
        }
        $role_permissions = RolePermission::whereIn('role_id', $role_ids)
            ->where('status', ENABLE)
            ->get();
        $permission_uris = [];
        $config_permissions = config('permissions');
        foreach ($role_permissions as $role_permission) {
            $permission_uris = array_merge($permission_uris, $config_permissions[$role_permission->permission_show_id]);
        }
        if (in_array($uri, $permission_uris)) {
            return false;
        } else {
            $res = ResponseService::returnArr([], '用户无权限访问该链接', 3002);
            return ResponseService::response($res);
        }
    }
}
