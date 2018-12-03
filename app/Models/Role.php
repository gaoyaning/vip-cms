<?php
namespace App\Models;

use App\Models\Permission;
use App\Services\ResponseService;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function rolePermissions() {
        return $this->hasMany('\App\Models\RolePermission')
            ->where('status', ENABLE);
    }
    public static function queryRoles($params) {
        $status = array_get($params, 'status', 2);
        0 == $status && $roles = self::where('status', DISABLE);
        1 == $status && $roles = self::where('status', ENABLE);
        2 == $status && $roles = self::whereIn('status', [ENABLE, DISABLE]);
       
        $name = array_get($params, 'name', '');
        '' != $name && $roles = $roles->where('name', $name);

        $page  = array_get($params, 'page', 0);
        $limit = array_get($params, 'page_size', 20);
        $roles = $roles->paginate($limit, ['id', 'name', 'desc', 'status'], 'page', $page)->toArray();
        $values = [
            'total' => $roles['total'],
            'list'  => $roles['data'],
        ];
        return ResponseService::returnArr($values);
    }

    public static function addRole($params) {
        $role = new self();
        $role->name = array_get($params, 'name', '');
        $role->desc = array_get($params, 'desc', '');
        $role->status = array_get($params, 'status', 1);
        $role->save();
        return $role;
    }

    public static function deleteRole($params) {
        $type = array_get($params, 'type', '');
        if (in_array($type, [0,1])) {
            self::whereIn('id', array_get($params, 'role_ids', []))
                ->update(['status' => $type]);
        }
    }

    public static function roleDetail($params) {
        $role = self::find($params['role_id']);
        $role_info = [
            'id'   => $role->id,
            'name' => $role->name,
            'desc' => $role->desc,
            'status' => $role->status,
        ];
        $role_permissions = $role->rolePermissions;
        $show_ids = [];
        foreach ($role_permissions as $role_permission) {
            $show_ids[] = $role_permission->permission_show_id;
        }
        $permissions = Permission::whereIn('show', $show_ids)
            ->where('parent_id', '0')
            ->where('menu', 1)
            ->get();
        $values = [];
        foreach ($permissions as $permission) {
            $child_menus = [];
            $childs = $permission->childs;
            foreach ($childs as $child) {
                if (!in_array($child->show, $show_ids)) {
                    continue;
                }
                $child_menus[] = [
                    'key' => $child->show,
                    'title' => $child->name,
                ];
            }
            $values[] = [
                'key' => $permission->show,
                'title' => $permission->name,
                'children' => $child_menus,
            ];
        }
        $role_info['permissions'] = $values;
        return $role_info;
    }

    public static function startRole($params) {
        $role = self::find($params['role_id']);
        $role->status = ENABLE;
        $role->save();
    }

    public static function modifyRole($params) {
        $role = self::find($params['role_id']);
        if (isset($params['name']) && '' != $params['name']) {
            $role->name = $params['name'];
        }
        if (isset($params['status'])) {
            $role->status = $params['status'];
        }
        $role->desc = array_get($params, 'desc', '');
        $role->save();
        return $role;
    }
}
