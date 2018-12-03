<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    const ONE_DAYS_LATER = 24 * 60 * 60;
    public function type() {
        return $this->belongsTo('App\Models\ProductType', 'product_type_id', 'id');
    }

    public function supplier() {
        return $this->belongsTo('App\Models\Supplier', 'supplier_id', 'id');
    }

    public static function queryByName($params) {
        $name = array_get($params, 'product_name', '');
        if ('' == $name) {
            return 0;
        }
        $product = self::where('name', $name)->first();
        if (!$product) {
            return 0;
        }
        return $product->id;
    }

    public static function queryInfo($params) {
        $page   = array_get($params, 'page', 1);
        $size   = array_get($params, 'page_size', 10);
        $status = array_get($params, 'status', 3);      // 0：待上架 1：已上架 2：已过期 3: 全部
        $name_code = array_get($params, 'name_or_code', '');
        $supplier_name    = array_get($params, 'supplier', '');
        $product_type_id  = array_get($params, 'category', '');
        if ($name_code > 0) {
            $name = $name_code;
            $code = '';
        } else {
            $code = $name_code;
            $name = '';
        }

        0 == $status && $products = self::where('status', $status);
        1 == $status && $products = self::where('status', $status);
        2 == $status && $products = self::where('deadline_time', '<=', time());
        3 == $status && $products = self::whereIn('status', [DISABLE, ENABLE]);
        '' != $name  && $products = $products->where('name', $name);
        '' != $code  && $products = $products->where('code', $code);
        '' != $supplier_name    && $products = $products->where('supplier_name', $supplier_name);
        '' != $product_type_id  && $products = $products->where('product_type_id', $product_type_id);
        $infos = $products->paginate($size, ['*'], 'page', $page)->toArray();
        $values = [
            'total' => $infos['total'],
        ];
        $lists = [];
        foreach ($infos['data'] as $list) {
            $deadtime = $list['deadline_time'];
            if (time() > $deadtime && 0 != $deadtime) {
                $list['status'] = 2;
                if (DISABLE == $status || ENABLE == $status) {
                    continue;
                }
            }
            if (2 == $status && 0 == $deadtime) {
                continue;
            }
            $lists[] = [
                'id'       => $list['id'],               // 商品id
                'code'     => $list['code'],             // 商品识别码
                'name'     => $list['name'],             // 商品名称
                'category' => $list['product_type_id'],  // 商品分类id
                'supplier' => $list['supplier_name'],    // 商品供应商
                'total'    => $list['total'],            // 商品总数
                'status'   => $list['status'],           // 商品状态（0：待上架 1：已上架 2：已过期）
            ];
        }
        $values['list'] = $lists;
        return $values;
    }

    public static function addInfo($params) {
        $product = new self();
        $product->code = $params['code'];
        $product->name = $params['name'];
        $product->product_type_id = $params['category'];
        $product->supplier_name   = $params['supplier'];
        $product->deadline_time   = $params['expiration'];

        $product->base_price = $params['base_price'];
        $product->status     = $params['status'];
        $product->content    = array_get($params, 'content', '');

        if (is_array($params['pics'])) {
            $product->pic_url = json_encode($params['pics']);
        } else {
            $product->pic_url = $params['pics'];
        }
        $product->save();
        return [];
    }

    public static function allInfo($params) {
        $products = self::all();
        $values = [];
        foreach ($products as $product) {
            $values[] = [
                'name'   => $product->name,
                'value'  => $product->id,
                'status' => $product->status,
            ];
        }
        return $values;
    }

    public static function deleteInfo($params) {
        $ids = array_get($params, 'ids', []);
        self::whereIn('id', $ids)->update(['status' => ERASE]);
        return [];
    }

    public static function updateInfo($params) {
        $product = self::find($params['id']);
        $product->status = $params['type'];
        $product->save();
        return [];
    }

    public static function modifyInfo($params) {
        $product = self::find($params['id']);
        $product->code = $params['code'];
        $product->name = $params['name'];
        $product->product_type_id = $params['category'];
        $product->supplier_name   = $params['supplier'];
        $product->deadline_time   = $params['expiration'];

        $product->base_price = $params['base_price'];
        $product->status     = $params['status'];
        $product->content    = array_get($params, 'content', '');

        if (is_array($params['pics'])) {
            $product->pic_url = json_encode($params['pics']);
        } else {
            $product->pic_url = $params['pics'];
        }
        $product->save();
        return [];
    }

    public static function infoDetail($params) {
        $product = self::find($params['id']);
        $pics = json_decode($product->pic_url, true);
        if (!$pics) {
            $pics = $product->pic_url;
        }
        $status = $product->status;
        if (time() > $product->deadline_time && 0 != $product->deadline_time) {
            $status = 2;
        }
        return [
            'id'         => $product->id,
            'code'       => $product->code,
            'name'       => $product->name,
            'category'   => $product->product_type_id,
            'supplier'   => $product->supplier_name,
            'base_price' => $product->base_price,
            'expiration' => $product->deadline_time,
            'total'      => $product->total,
            'status'     => $status,
            'pics'       => $pics,
            'content'    => $product->content,
        ];
    }

    public static function allRights() {
        $product_types = ProductType::all();
        $type_names = [];
        foreach ($product_types as $product_type) {
            $type_names[$product_type->id] = $product_type->name;
        }
        $products = self::where('status', ENABLE)
            ->get();
        $right_infos = [];
        foreach ($products as $product) {
            if (!isset($right_infos[$product->product_type_id])) {
                $right_infos[$product->product_type_id] = [
                    'key'   => "$product->product_type_id",
                    'title' => $type_names[$product->product_type_id],
                    'children' => [],
                ];
            }
            $right_infos[$product->product_type_id]['children'][] = [
                'key'   => $product->product_type_id .'-'. $product->id,
                'title' => $product->name,
            ];
        }
        $rights = [];
        foreach ($right_infos as $right) {
            $rights[] = $right;
        }
        return $rights;
    }

    public static function getNoticeProducts($ids) {
        return self::whereIn('id', $ids)
            ->whereBetween('deadline_time', [time(), time() + self::ONE_DAYS_LATER * 30])
            ->get();
    }

    public static function queryProductDetail($params) {
        $product = self::find(array_get($params, 'id', 0));
        return [
            'id'      => $product->id,
            'urls'    => json_decode($product->pic_url, true),
            'content' => $product->content,
        ];
    }
}
