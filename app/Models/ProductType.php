<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    public static function getProductByType($type) {
        return Product::where('product_type_id', $type)
            ->where('status', '>', ERASE)
            ->count();
    }

    public static function queryCategory($params) {
        $page = array_get($params, 'page', 1);
        $size = array_get($params, 'page_size', 10);
        $categories = self::whereIn('status', [DISABLE, ENABLE])
            ->orderBy('order');
        $name = array_get($params, 'name', '');
        '' != $name && $categories->where('name', $name);
        $types = $categories->paginate($size, ['*'], 'page', $page)->toArray();
        $lists = [];
        $values = [
            'total' => $types['total'],
            'list'  => [],
        ];
        foreach ($types['data'] as $list) {
            $lists[] = [
                'id'    => $list['id'],
                'icon'  => $list['icon'],
                'name'  => $list['name'],
                'desc'  => $list['desc'],
                'total' => self::getProductByType($list['id']),
            ];
        }
        $values['list'] = $lists;
        return $values;
    }

    public static function addCategory($params) {
        $values = [];
        $name = array_get($params, 'name', '');
        $icon = array_get($params, 'icon', '');
        $desc = array_get($params, 'desc', '');
        $category = new self();
        $category->name = $name;
        $category->icon = $icon;
        $category->desc = $desc;
        $category->save();
        return $values;
    }

    public static function updateCategory($params) {
        $values = [];
        $ids = array_get($params, 'ids', []);
        $type = array_get($params, 'type', '');
        if (in_array($type, [-1, 0])) {
            self::whereIn('id', $ids)
                ->update(['status' => $type]);
        }
        return $values;
    }

    public static function modifyCategory($params) {
        $values = [];
        $category = self::find($params['id']);
        if ($category) {
            $category->name = $params['name'];
            $category->icon = $params['icon'];
            $category->desc = $desc = array_get($params, 'desc', '');
            $category->save();
        }
        return $values;
    }

    public static function categoryDetail($params) {
        $category = self::find($params['id']);
        $values = [];
        if (!$category) {
            return $values;
        }
        $values = [
            'id'    => $category->id,
            'icon'  => $category->icon,
            'name'  => $category->name,
            'desc'  => $category->desc,
            'total' => self::getProductByType($category->id),
        ];
        return $values;
    }

    public static function topCategory($params) {
        $category = self::find($params['id']);
        $order = $category->order;
        self::where('order', '<', $order)
            ->increment('order');
        $category->order = 1;
        $category->save();
        return [];
    }

    public static function ascentCategory($params) {
        $category = self::find($params['id']);
        if (1 == $category->order) {
            self::where('order', 1)
                ->where('id', '!=', $params['id'])
                ->increment('order');
            return [];
        }
        self::where('order', $category->order - 1)
            ->increment('order');
        $category->order = $category->order - 1;
        $category->save();
        return [];
    }

    public static function descentCategory($params) {
        $category = self::find($params['id']);
        self::where('order', $category->order + 1)
            ->decrement('order');
        $category->order = $category->order + 1;
        $category->save();
        return [];
    }

    public static function navigation() {
        $all = self::where('status', ENABLE)
            ->orderBy('order')
            ->get();
        $values = [];
        foreach ($all as $one) {
            $values[] = [
                'key'   => $one->id,
                'title' => $one->name,
            ];
        }
        return $values;
    }
}
