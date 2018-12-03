<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public function childs() {
        return $this->hasMany('App\Models\Permission', 'parent_id', 'show')
            ->whereIn('status', [0,1]);
    }
    public static function getUserMenus() {
        $menu_objs = self::whereIn('status', [DISABLE, ENABLE])
            ->where('menu', 1)
            ->orderBy('order')
            ->get();
        $menus = [];
        foreach ($menu_objs as $obj) {
            $item = [
                'id'    => $obj->show,      // 菜单id
                'icon'  => $obj->icon,    // 菜单icon
                'name'  => $obj->name,    // 菜单名称
                'route' => $obj->route,     // 菜单路由
            ];
            if ($obj->parent_id > 0) {
                $item['mpid'] = $obj->parent_id;    // 父菜单id，为-1表示不在导航菜单栏显示
                $item['bpid'] = $obj->parent_id;    // 父菜单id
                if (0 == $obj->status) {
                    $item['mpid'] = '-1';
                }
            }
            $menus[] = $item;
        }
        return $menus;
    }

    public static function permissions() {
        $permissions = self::whereIn('status', [DISABLE, ENABLE])
            ->where('parent_id', '0')
            ->where('menu', 1)
            ->orderBy('order')
            ->get();
        $values = [];
        foreach ($permissions as $permission) {
            $child_menus = [];
            $childs = $permission->childs;
            foreach ($childs as $child) {
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
        return $values;
    }

    public static function getPermissionsByShowIds($show_ids) {
        $permissions = self::whereIn('show', $show_ids)
            ->get();
    } 
}
