<?php
namespace App\Models;

use App\Services\ResponseService;
use Illuminate\Database\Eloquent\Model;

class ReceiveRecord extends Model
{
    public static function getUserReceiveRecords($params) {
        $records = self::where('telco_id', $params['telco_id'])
            ->where('partner_user_id', $params['user']['id'])
            ->get();
        $arr = [];
        foreach ($records as $record) {
            $arr[$record->product_id] = $record;
        }
        return $arr;
    }

    public static function setUserReceiveRecord($params, $code) {
        $record = new self();
        $partner_user_id = array_get($params, 'user.id', 0);
        $telco_id = array_get($params, 'user.telco_id', 0);
        $level_id = array_get($params, 'user.level_id', 0);
        $product_id = array_get($params, 'product_id', 0);
        $record->coupon   = $code;
        $record->telco_id = $telco_id;
        $record->level_id = $level_id;
        $record->product_id = $product_id;
        $record->partner_user_id = $partner_user_id;
        $record->save();
    }

    public static function getReceivedProductRecord($params) {
        $partner_user_id = array_get($params, 'user.id', 0);
        $telco_id   = array_get($params, 'user.telco_id', 0);
        $level_id   = array_get($params, 'user.level_id', 0);
        $product_id = array_get($params, 'product_id', 0);
        $record = self::where('partner_user_id', $partner_user_id)
            ->where('product_id', $product_id)
            ->where('telco_id', $telco_id)
            ->where('level_id', $level_id)
            ->first();
        return $record;
    }
}
