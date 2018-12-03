<?php
namespace App\Models;

use App\Services\ResponseService;
use Illuminate\Database\Eloquent\Model;

class Recommend extends Model
{
    public function level() {
        return $this->belongsTo('App\Models\Level', 'level_id', 'id');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    public function productForUpdate() {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id')
            ->lockForUpdate();
    }

    public function telco() {
        return $this->belongsTo('App\Models\Telco', 'telco_id', 'id');
    }

    public function right() {
        return $this->belongsTo('App\Models\Right', 'right_id', 'id');
    }

    public function rightForUpdate() {
        return $this->belongsTo('App\Models\Right', 'right_id', 'id')
            ->lockForUpdate();
    }

    public static function queryClassifyByLevel($params, $arr = []) {
        $telco_id = $params['telco_id'];
        $recommends = self::where('telco_id', $telco_id);
        $level_id = array_get($params, 'level_id', '');
        '' != $level_id && $recommends = $recommends->where('level_id', $level_id);
        $recommends = $recommends->where('status', 1)
            ->orderBy('level_id')
            ->orderBy('order')
            ->get();
        // 获取商品ID 对应的名称
        $product_types = ProductType::all();
        $type_names = [];
        foreach ($product_types as $product_type) {
            $type_names[$product_type->id] = $product_type->name;
        }

        $filter = [];
        $recommend_lists = [];
        foreach ($recommends as $recommend) {
            if (in_array($recommend->product_id, $filter)) {
                continue;
            }
            $product = $recommend->product;
            if (!isset($recommend_lists[$product->product_type_id])) {
                $recommend_lists[$product->product_type_id] =  [
                    'key'   => $product->product_type_id,
                    'title' => $type_names[$product->product_type_id],
                    'lists' => [],
                ];
            }
            if (0 != $product->deadline_time && time() > $product->deadline_time) {
                $status = 2;
            } else {
                $status = 0;
            }
            $coupon = '';
            if (isset($arr[$product->id])) {
                $status = 1;
                $coupon = $arr[$product->id]->coupon;
            }
            if ($product->deadline_time == 0) {
                $end_time = '2099/12/31';
            } else {
                $end_time = date('Y/m/d', $product->deadline_time);
            }
            $urls = json_decode($product->pic_url, true);
            if (!is_array($urls)) {
                $urls = [];
            }
            $recommend_lists[$product->product_type_id]['lists'][] = [
                'id'       => $product->id,
                'urls'     => json_decode($product->pic_url, true),
                'name'     => $product->name,
                'subTitle' => $product->sub_title,
                'status'   => $status,
                'coupon'   => $coupon,
                'start_time' =>  date('Y/m/d', time()),
                'end_time'   =>  $end_time,
            ];
        }
        $values = [];
        sort($recommend_lists, SORT_NUMERIC);
        foreach ($recommend_lists as $list) {
            $values[] = $list;
        }
        return $values;
    }

    public static function queryRecommends($params) {
        $status = array_get($params, 'status', 2);
        if (2 == $status) {
            $recommends = self::whereIn('status', [DISABLE, ENABLE]);
        } else {
            $recommends = self::where('status', $status);
        }
        $telco_id   = array_get($params, 'telco_id', '');
        $product_id = array_get($params, 'product_id', '');
        '' != $telco_id    && $recommends = $recommends->where('telco_id', $telco_id);
        '' != $product_id && $recommends = $recommends->where('product_id', $product_id);
        
        $page  = array_get($params, 'page', 1);
        $limit = array_get($params, 'page_size', 20);

        $recommends = $recommends->orderBy('order', 'asc')
            ->paginate($limit, ['*'], 'page', $page)->toArray();
        
        $list = [];
        foreach ($recommends['data'] as $recommend_info) {
            $recommend = self::find($recommend_info['id']);
            $goods = $recommend->product;
            $telco = $recommend->telco;
            $level = $recommend->level;
            $list[] = [
                'id'           => $recommend->id,
                'telco_id'     => $telco->id,
                'operator'     => $telco->name,         // 运营商名称
                'product_no'   => $goods->code,         // 运营商代码
                'product_name' => $goods->name,         // 商品名称
                'product_type' => $goods->type,         // 商品类型
                'product_code' => $goods->code,         // 权益商品编码
                'level_name'   => $level->level,        // 等级
                'level_id'     => $level->id,           // 等级id
                'status'       => $recommend->status,   // 推荐状态
                'operation'    => '',                   // 操作类型
                'config_time'  => $recommend_info['created_at'],  // 配置时间
            ];
        }
        return ['list' => $list, 'total' => $recommends['total']];
    }

    public static function recommendDetail($params) {
        $telco_id   = array_get($params, 'telco_id', 0);
        $level_id   = array_get($params, 'level_id', 0);
        $recommends = self::where('telco_id', $telco_id)
            ->where('level_id', $level_id)
            ->where('status', ENABLE)
            ->get();
        if (empty($recommends)) {
            return [];
        }

        $telco = $recommends[0]->telco;
        $level = $recommends[0]->level;

        $values = [
            'telco_id'   => $telco->id,
            'telco_code' => $telco->code,
            'telco_name' => $telco->name,
            'level'      => $level->level,
            'status'     => $recommends[0]->status,
        ];
        $prduct_info = [];
        foreach ($recommends as $recommend) {
            $product = $recommend->product;
            $product_type = $product->type;
            if (!isset($product_info[$product_type->id])) {
                $product_info[$product_type->id] = [
                    'key'   => "$product_type->id",
                    'title' => $product_type->name,
                    'children'  => [],
                ];
            }
            $product_info[$product_type->id]['children'][] = [
                'key'   => $product_type->id .'-'. $product->id,
                'title' => $product->name,
            ];
        }

        $recommend_info = [];
        foreach ($product_info as $recommend) {
            $recommend_info[] = $recommend;
        }

        $values['recommends'] = $recommend_info;
        return $values;
    }

    public static function topRecommend($params) {
        $id = array_get($params, 'id', 0);
        $recommend = self::find($id);
        // 优先级高的数据+1
        self::where('telco_id', $recommend->telco_id)
            ->where('level_id', $recommend->level_id)
            ->where('order', '<', $recommend->order)
            ->increment('order', 1);

        $recommend->order = 1;
        $recommend->save();
    }

    public static function updateRecommend($params) {
        $id = array_get($params, 'id', 0);
        $status = $params['type'];
        $recommend = self::find($id);
        $recommend->status = $status;
        $recommend->save();
    }

    public static function addRecommend($params) {
        $telco_id = $params['telco_id'];
        $level_id = $params['level_id'];
        $status   = array_get($params, 'status', DISABLE);
        $product_infos = array_get($params, 'product_info', []);

        $products = [];
        foreach ($product_infos as $product_info) {
            foreach ($product_info['children'] as $order => $product) {
                list($type_id, $product_id) = explode('-', $product['key']);
                $products[$product_id] = [
                    'id'    => $product_id,
                    'order' => $order,
                ];
            }
        }

        $recommends = self::where('telco_id', $telco_id)
            ->where('level_id', $level_id)
            ->get();
        foreach ($recommends as $recommend) {
            if (isset($products[$recommend->product_id])) {
                $recommend->status = $status;
                $order = array_get($products[$recommend->product_id], 'order', '');
                if ('' != $order) {
                    $recommend->order = $order;
                }
                $recommend->status = $status;
                $recommend->save();
                unset($products[$recommend->product_id]);
            } else {
                $recommend->status = 0;
                $recommend->save();
            }
        }

        foreach ($products as $product) {
            $recommend = new self();
            $recommend->status   = $status;
            $recommend->order    = $product['order'];
            $recommend->telco_id = $telco_id;
            $recommend->level_id = $level_id;
            $recommend->product_id = $product['id'];
            $recommend->save();
        }
    }

    public static function modifyRecommend($params) {
        return self::addRecommend($params);
    }

    public static function orderRecommend($params) {
        $id   = $params['id'];
        $type = $params['type'];
        $recommend = self::find($id);
        //
        if (-1 == $type) {
            $recommends = self::where('telco_id', $recommend->telco_id)
                ->where('level_id', $recommend->level_id)
                ->where('order', $recommend->order - 1);
            if (count($recommends->get())) {
                $recommends->increment('order');
                $recommend->order = $recommend->order + $type;
            }
        } elseif (1 == $type) {
            $recommends = self::where('telco_id', $recommend->telco_id)
                ->where('level_id', $recommend->level_id)
                ->where('order', $recommend->order + 1);
            if (count($recommends->get()) > 0) { 
                $recommends->decrement('order');
                $recommend->order = $recommend->order + $type;
            }
        }
        $recommend->save();
        return;
    }

    // 批量删除
    public static function deleteRecommend($params) {
        $ids = array_get($params, 'ids', []);
        self::whereIn('id', $ids)
            ->update(['status' => ERASE]);
    }

    public static function getRecommendsByTelcoLevelId($params) {
        return self::where('telco_id', $params['telco_id'])
            ->where('level_id', $params['level_id'])
            ->where('status', ENABLE)
            ->get();
    }

    public static function hasRecommendRight($params) {
        $product_id = array_get($params, 'product_id', 0);
        $telco_id = array_get($params, 'user.telco_id', 0);
        $level_id = array_get($params, 'user.level_id', 0);
        $recommend = self::where('telco_id', $telco_id)
            ->where('level_id', $level_id)
            ->where('product_id', $product_id)
            ->first();
        if ($recommend) {
            return $recommend;
        }
        return false;
    }
}
