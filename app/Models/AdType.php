<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdType extends Model
{
    public static function queryAdTypes($params) {
        $adTypes = self::where('status', ENABLE)
            ->get();
        $types = [];
        foreach ($adTypes as $type) {
            if (!isset($types[$type->page_type])) {
                $types[$type->page_type] = [
                    'name'  => $type->page_type_name,
                    'value' => $type->page_type,
                    'lists' => [],
                ];
            }
            $types[$type->page_type]['lists'][] = [
                'name'  => $type->page_pos_name,
                'value' => $type->id,
            ];
        }
        $values = [];
        foreach ($types as $list) {
            $values[] = $list;
        }
        return $values;
    }

    public static function addAdType($params) {
        $adType = new self();
        $adType->page_type = $params['page_type'];
        $adType->page_pos  = $params['page_pos'];
        $adType->page_type_name = $params['page_type_name'];
        $adType->page_pos_name  = $params['page_pos_name'];
        $adType->save();
    }

    public static function getAdTypes($params) {
        $adTypes = self::where('status', ENABLE)
            ->where('page_type', array_get($params, 'page_type', 0))
            ->where('page_pos', array_get($params, 'page_pos', 0))
            ->get();
        return $adTypes;
    }
}
