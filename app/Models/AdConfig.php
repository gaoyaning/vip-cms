<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdConfig extends Model
{
    public function adType() {
        return $this->belongsTo('App\Models\AdType', 'ad_type_id', 'id');
    }
    public function telco() {
        return $this->belongsTo('App\Models\Telco', 'telco_id', 'id');
    }
    public static function queryAds($params) {
        $telco_id   = array_get($params, 'telco_id', '');
        $ads_name   = array_get($params, 'ads_name', '');
        $ad_type_id = array_get($params, 'ads_pos',  0);
        $limit      = array_get($params, 'page_size', 20);
        $page       = array_get($params, 'page', 1);

        $ads = self::whereIn('status', [DISABLE, ENABLE]);
        '' != $telco_id   && $ads = $ads->where('telco_id', $telco_id);
        '' != $ads_name   && $ads = $ads->where('ad_name', $ads_name);
        0  != $ad_type_id && $ads = $ads->where('ad_type_id', $ad_type_id);

        $ads = $ads->paginate($limit, ['*'], 'page', $page)->toArray();
        $values = [
            'total' => $ads['total'],
            'list'  => [],
        ];
        foreach ($ads['data'] as $ad) {
            $adObj = self::find($ad['id']);
            $adType = $adObj->adType;
            $telco  = $adObj->telco;
            $values['list'][] = [
                'id'         => $ad['id'],
                'ads_no'     => $ad['id'],
                'ads_img'    => $ad['ad_pic'],
                'ads_name'   => $ad['ad_name'],
                'ads_pos_no' => $ad['ad_type_id'],
                'operator'   => $telco->name,
                'ads_page'   => $adType->page_type_name, 
                'ads_pos'    => $adType->page_pos_name,     
                'ads_type'   => $ad['ad_type'],
                'ads_link'   => $ad['ad_link'],
                'ads_pic'    => $ad['ad_pic'],
                'pic_size'   => $ad['pic_size'],
                'start_time' => date('Y-m-d H:i:s', $ad['start_time']),
                'end_time'   => date('Y-m-d H:i:s', $ad['ended_time']),
                'status'     => $ad['status'],
            ];
        }
        return $values;
    }

    public static function adDetail($params) {
        $ad = self::find($params['id']);
        $adType = $ad->adType;
        $telco  = $ad->telco;
        return [
            'ads_no'     => $ad->id,
            'ads_img'    => $ad->ad_pic,
            'ads_name'   => $ad->ad_name,
            'ads_pos_no' => $ad->ad_type_id,
            'operator'   => $telco->name,
            'telco_id'   => $ad->telco_id,
            'ads_page'   => $adType->page_type,
            'ads_pos'    => $adType->page_pos, 
            'ads_type'   => $ad->ad_type,
            'ads_link'   => $ad->ad_link,
            'ads_pic'    => $ad->ad_pic,
            'pic_size'   => $ad->pic_size,
            'start_time' => date('Y-m-d H:i:s', $ad->start_time),
            'end_time'   => date('Y-m-d H:i:s', $ad->ended_time),
            'status'     => $ad->status,
        ];
    }

    public static function addAd($params) {
        $ad = new self(); 
        $ad->telco_id   = array_get($params, 'telco_id', '');
        $ad->ad_type_id = array_get($params, 'ads_pos_id', '');
        $ad->pic_size   = array_get($params, 'ads_size', '');
        $ad->ad_type    = array_get($params, 'ads_type', '');
        $ad->ad_pic     = array_get($params, 'ads_img', '');
        $ad->ad_name    = array_get($params, 'ads_name', '');
        $ad->ad_link    = array_get($params, 'ads_link', '');
        $ad->status     = array_get($params, 'status', DISABLE);
        $ad->start_time = strtotime(array_get($params, 'start_time', 0));
        $ad->ended_time = strtotime(array_get($params, 'end_time', 0));
        $ad->save();
        return [
            'ads_no' => $ad->id,
        ];
    }

    public static function updateAdStatus($params) {
        $ad   = self::find($params['id']);
        $ad->status = $params['type'];
        $ad->save();
    }

    public static function deleteAds($params) {
        $ids = array_get($params, 'ids', []);
        self::whereIn('id', $ids)
            ->update(['status' => -1]);
    }

    public static function modifyAd($params) {
        $ad = self::find($params['id']);
        $telco_code = array_get($params, 'operator', '');
        $ad_page    = array_get($params, 'adsPage', '');
        $ad_pos     = array_get($params, 'adsPos', '');
        $ad_type    = array_get($params, 'adsType', '');
        $ad_size    = array_get($params, 'adsSize', '');
        $ad_code    = array_get($params, 'adsNo', '');
        $ad_pic     = array_get($params, 'adsImg', '');
        $ad_name    = array_get($params, 'adsName', '');
        $ad_link    = array_get($params, 'adsLink', '');
        $status     = array_get($params, 'status', '');
        $range_date = array_get($params, 'rangeDate', '');
        if ('' != $rangeDate) {
            list($start_time, $ended_time) = explode('~', $params['range_date']);
            $ad->start_time = strtotime($start_time);
            $ad->ended_time = strtotime($ended_time);
        }
        '' != $telco_code && $ad->telco_code = $telco_code;
        '' != $ad_page    && $ad->ad_page = $ad_page;
        '' != $ad_pos     && $ad->ad_pos  = $ad_pos;
        '' != $ad_type    && $ad->ad_type = $ad_type;
        '' != $ad_size    && $ad->ad_size = $ad_size;
        '' != $ad_code    && $ad->ad_code = $ad_code;
        '' != $ad_pic     && $ad->ad_pic  = $ad_pic;
        '' != $ad_name    && $ad->ad_name = $ad_name;
        '' != $ad_link    && $ad->ad_link = $ad_link;
        if ('' != $status && in_array($status, [ERASE, ENABLE, DISABLE])) {
            $ad->status = $stauts;
        }
        $ad->save();
    }

    public static function getUserAds($params, $ad_type_ids) {
        $ad_configs = self::where('telco_id', array_get($params, 'telco_id', 0))
            ->whereIn('ad_type_id', $ad_type_ids)
            ->where('status', ENABLE)
            ->get();
        $values = [];
        foreach ($ad_configs as $ad_config) {
            $values[] = [
                'url'  => $ad_config->ad_pic,
                'name' => $ad_config->ad_name,
                'link' => $ad_config->ad_link,
            ];
        }
        return $values;
    }
}
