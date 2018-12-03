<?php
namespace App\Models;

use App\Services\ResponseService;
use Illuminate\Database\Eloquent\Model;

class PartnerUser extends Model
{
    public function telco() {
        return $this->belongsTo('App\Models\Telco', 'telco_code', 'code');
    }

    public function vipUser() {
        return $this->belongsTo('App\Models\VipUser', 'uid', 'id');
    }

    public function level() {
        return $this->belongsTo('App\Models\Level', 'level_id', 'id');
    }

    public static function getLevel($telco_id, $level) {
        $level = Level::where('telco_id', $telco_id)
            ->whereIn('status', [DISABLE, ENABLE])
            ->where('level', $level)
            ->first();
        return $level;
    }

    public static function getOrCreateUserByTecoTid($params) {
        $user = self::where('telco_code', $params['partner'])
            ->where('tid', $params['union_id'])
            ->first();
        if (!$user) {
            $user = new self();
            $user->telco_code = $params['partner'];
            $user->tid        = $params['union_id'];
            $user->status     = ENABLE;
        }
        $telco = $user->telco;
        // 根据level信息获取level_id & 更新ID
        $level = self::getLevel($telco->id, $params['level']);
        if (!$level) {
            return ResponseService::returnArr([], '等级信息错误', 70001);
        }
        $user->level_id = $level->id;
        $user->telco_id = $telco->id;
        $user->save();
        return $user;
    }

    public static function getUserById($params) {
        $partner_user = self::find(array_get($params, 'user.id', 0));
        $vip_user     = $partner_user->vipUser;
        $level        = $partner_user->level;
        $values = [];
        $oauth = false;
        if ($vip_user) {
            $oauth = true;
        }
        if ($vip_user) {
            $values['name']     = $vip_user->name;
            $values['link']     = $partner_user->image;
            $values['level']    = $level->level;
            $valuse['oauth']    = $oauth;
            $values['user_id']  = $partner_user->id;
            $values['level_id'] = $level->id;
        } else {
            $values['name']     = $partner_user->tid;
            $values['link']     = $partner_user->image;
            $values['level']    = $level->level;
            $valuse['oauth']    = $oauth;
            $values['user_id']  = $partner_user->id;
            $values['level_id'] = $level->id;
        }
        return $values;
    }
}
