<?php
namespace App\Models;

use App\Services\ResponseService;
use Illuminate\Database\Eloquent\Model;

class Right extends Model
{
    public function level() {
        return $this->belongsTo('App\Models\Level', 'level_id', 'id');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    public function telco() {
        return $this->belongsTo('App\Models\Telco', 'telco_id', 'id');
    }

    public static function queryRights($params) {
        $status = array_get($params, 'status', 2);
        if (2 == $status) {
            $rights = self::whereIn('status', [DISABLE, ENABLE]);
        } else {
            $rights = self::where('status', $status);
        }
        
        $telco_id   = array_get($params, 'telco_id', '');
        $product_id = array_get($params, 'product_id', '');
        $range_date = array_get($params, 'range_date', '');
        if ('' != $range_date) {
            list($start, $ended) = explode(',', $range_date);
            if ($start && $ended) {
                $start_time = date('Y-m-d H:i:s', strtotime($start));
                $ended_time = date('Y-m-d H:i:s', strtotime($start));
                $rights = $rights->where('created_at', '>=', $start_time)
                    ->where('created_at', '<=', $ended_time);
            }
        }
        $total = array_get($params, 'product_num', '');
        if ('' != $total) {
            list($start, $ended) = explode(',', $total);
            $rights = $$rights->where('total', '>=', $start)
                ->where('total', '<=', $ended);
        }
        '' != $telco_id   && $rights = $rights->where('telco_id', $telco_id);
        '' != $product_id && $rights = $rights->where('product_id', $product_id);
        
        $page  = array_get($params, 'page', 1);
        $limit = array_get($params, 'page_size', 20);

        $rights = $rights->paginate($limit, ['*'], 'page', $page)->toArray();
        
        $list = [];
        foreach ($rights['data'] as $right_info) {
            $right = self::find($right_info['id']);
            $goods = $right->product;
            $telco = $right->telco;
            $level = $right->level;
            $list[] = [
                'id'           => $right->id,
                'telco_id'     => $telco->id,           // 运营商id
                'telco_name'   => $telco->name,         // 运营商名称
                'telco_code'   => $telco->code,         // 运营商代码
                'product_name' => $goods->name,         // 商品名称
                'product_type' => $goods->type,         // 商品类型
                'product_code' => $goods->code,         // 权益商品编码
                'product_num'  => $right->total,        // 商品数量
                'product_left' => $right->total - $right->consum, // 商品数量
                'level_name'   => $level->level,        // 等级
                'level_id'     => $level->id,           // 等级id
                'status'       => $right->status,       // 推荐状态
                'operation'    => '',                   // 操作类型
                'config_time'  => $right_info['created_at'],   // 配置时间
            ];
        }
        return ResponseService::returnArr(['list' => $list, 'total' => $rights['total']]);
    }

    public static function rightDetail($params) {
        $telco_id   = array_get($params, 'telco_id', 0);
        $level_id   = array_get($params, 'level_id', 0);
        $rights = self::where('telco_id', $telco_id)
            ->where('level_id', $level_id)
            ->where('status', ENABLE)
            ->get();
        if (empty($rights)) {
            return [];
        }

        $telco = $rights[0]->telco;
        $level = $rights[0]->level;

        $values = [
            'telco_id'   => $telco->id,
            'telco_code' => $telco->code,
            'telco_name' => $telco->name,
            'level'      => $level->level,
            'status'     => $rights[0]->status,
        ];

        $product_types = ProductType::all();
        $type_names = [];
        foreach ($product_types as $product_type) {
            $type_names[$product_type->id] = $product_type->name;
        }

        $product_info = [];
        foreach ($rights as $right) {
            $product = $right->product;
            if (!isset($product_info[$product->product_type_id])) {
                $product_info[$product->product_type_id] = [
                    'key'   => "$product->product_type_id",
                    'title' => $type_names[$product->product_type_id],
                    'children' => [],
                ];
            }

            $product_info[$product->product_type_id]['children'][] = [
                'key'   => $product->product_type_id .'-'. $product->id,
                'title' => $product->name,
                'value' => $right->total,
            ];
        }

        $rights = [];
        foreach ($product_info as $product) {
            $rights[] = $product;
        }

        $values['rights'] = $rights;
        return $values;
    }


    public static function updateRight($params) {
        $id = array_get($params, 'id', 0);
        $status = $params['type'];
        $right = self::find($id);
        $right->status = $status;
        $right->save();
    }

    public static function addRight($params) {
        $telco_id = $params['telco_id'];
        $level_id = $params['level_id'];
        $status   = array_get($params, 'status', DISABLE);
        $product_infos = array_get($params, 'products', []);

        $products = [];
        foreach ($product_infos as $product_children) {
            $children = array_get($product_children, 'children', []);
            foreach ($children as $child) {
                list($type_id, $product_id) = explode('-', $child['key']);
                $products[$product_id] = [
                    'id' => $product_id,
                    'amount' => array_get($child, 'value', 0),
                ];
            }
        }

        $rights = self::where('telco_id', $telco_id)
            ->where('level_id', $level_id)
            ->get();
        foreach ($rights as $right) {
            if (isset($products[$right->product_id])) {
                $right->status = $status;
                $amount = array_get($products[$right->product_id], 'amount', '');
                if ('' != $amount) {
                    $right->total = $amount;
                }
                $right->save();
                unset($products[$right->product_id]);
            } else {
                $right->status = DISABLE;
                $right->save();
            }
        }

        foreach ($products as $product) {
            $right = new self();
            $right->status     = $status;
            $right->telco_id   = $telco_id;
            $right->level_id   = $level_id;
            $right->product_id = $product['id'];
            $right->total = $product['amount'];
            $right->save();
        }
    }

    public static function modifyRight($params) {
        return self::addRight($params);
    }

    // 批量删除
    public static function deleteRight($params) {
        /*
        $rights = array_get($params, 'rights', []);
        foreach ($rights as $right) {
            self::where('telco_id', $right['telco_id'])
                ->where('level_id', $right['level_id'])
                ->update(['status' => ERASE]);
        }*/
        $ids = array_get($params, 'ids', []);
        self::whereIn('id', $ids)
            ->update(['status' => ERASE]);
    }

    public static function updateRightStatus($params) {
        $type = array_get($params, 'type', 0);
        $ids  = array_get($params, 'ids', []);
        self::whereIn('id', $ids)
            ->update(['status' => $type]);
    }

    public static function rightsInfo($params) {
        $rights = self::where('status', ENABLE)
            ->get();
        $values = [];
        $products_info = [];
        $news = [];
        foreach ($rights as $right) {
            if ($right->total - $right->consum <= 0) {
                continue;
            }
            if (!isset($news[$right->telco_id])) {
                $news[$right->telco_id] = [];
            }
            if (!isset($news[$right->telco_id][$right->level_id])) {
                $news[$right->telco_id][$right->level_id] = [];
            }
            if (!isset($news[$right->telco_id][$right->level_id][$right->product->product_type_id])) {
                $news[$right->telco_id][$right->level_id][$right->product->product_type_id] = [];
            }
            if (!isset($news[$right->telco_id][$right->level_id][$right->product->product_type_id]['title'])) {
                $news[$right->telco_id][$right->level_id][$right->product->product_type_id]['title'] = $right->product->type->name;
                $product_type_id = $right->product->product_type_id;
                $news[$right->telco_id][$right->level_id][$right->product->product_type_id]['key'] = "$product_type_id";
            }
            if (!isset($news[$right->telco_id][$right->level_id][$right->product->product_type_id]['children'])) {
                $news[$right->telco_id][$right->level_id][$right->product->product_type_id]['children'] = [];
            }
            $news[$right->telco_id][$right->level_id][$right->product->product_type_id]['children'][] = [
                'key' => $right->product->product_type_id. '-' .$right->product->id,
                'title' => $right->product->name,
            ];
        }
        foreach ($news as $telco_id => $telcoes) {
            if (!isset($values[$telco_id])) {
                $values[$telco_id] = [];
            }
            foreach ($telcoes as $level_id => $levels) {
                if (!isset($values[$telco_id][$level_id])) {
                    $values[$telco_id][$level_id] = [];
                }
                foreach ($levels as $product_type_id => $value) {
                    $values[$telco_id][$level_id][] = $value;
                }
            }
        }
        return ResponseService::returnArr($values);
    }
}
