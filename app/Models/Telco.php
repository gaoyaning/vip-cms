<?php
namespace App\Models;

use App\Services\ResponseService;
use Illuminate\Database\Eloquent\Model;

class Telco extends Model
{
    public function levels() {
        return $this->hasMany('\App\Models\Level');
    }
    public static function addTelco($params) {
        $telco = new self();
        $telco->code = $params['code'];
        $telco->name = $params['name'];
        $telco->city = $params['city'];
        $telco->status   = ENABLE;   // 默认开启
        $telco->contact  = $params['contact'];
        $telco->address  = $params['address'];
        $telco->province = $params['province'];
        $telco->district = $params['district'];
        $telco->contact_mobile = $params['contact_mobile'];
        $telco->assigned_time  = date('Y-m-d H:i:s', time());
        $telco->save();
        return;
    }

    public static function queryByName($params) {
        $telco_name = array_get($params, 'telco_name', '');
        if ('' == $telco_name) {
            return 0;
        }
        $telco = self::where('name', $telco_name)->first();
        if ($telco) {
            return $telco->id;
        }
        return 0;
    }

    public static function telcoInfo($params) {
        $telcoes = self::where('status', ENABLE)
            ->get();
        $values = [];
        foreach ($telcoes as $k => $telco) {
            $levels = $telco->levels;
            $values[] = [
                'name'  => $telco->name,
                'code'  => $telco->code,
                'value' => $telco->id,
            ];
            foreach ($levels as $level) {
                $values[$k]['levels'][] = [
                    'name'  => $level->level,
                    'value' => $level->id,
                ];
            }
        }
        return $values;
    }

    public static function modifyTelco($params) {
        $telco = self::find($params['id']);
        '' != array_get($params, 'code', '') && $telco->code = $params['code'];
        '' != array_get($params, 'name', '') && $telco->name = $params['name'];
        '' != array_get($params, 'city', '') && $telco->city = $params['city'];
        '' != array_get($params, 'contact', '') && $telco->contact = $params['contact'];
        '' != array_get($params, 'province', '') && $telco->province = $params['province'];
        '' != array_get($params, 'address', '') && $telco->address = $params['address'];
        '' != array_get($params, 'district', '') && $telco->district = $params['district'];
        '' != array_get($params, 'contact_name', '') && $telco->contact_name = $params['contact_name'];
        $telco->save();
        return;
    }

    public static function deleteTelco($params) {
        $ids = array_get($params, 'ids', []);
        $telco = self::whereIn('id', $ids)
            ->update(['status' => ERASE]);
        return;
    }

    public static function updateTelco($params) {
        $telco = self::find($params['id']);
        $telco->status = $params['type'];
        $telco->save();
    }

    public static function queryTelcoes($params) {
        $status = array_get($params, 'status', 2);
        if (2 == $status) {
            $telcoes = self::whereIn('status', [ENABLE, DISABLE]);
        } else {
            $telcoes = self::where('status', DISABLE);
        }
        $telco_id = array_get($params, 'telco_id', '');
        $contact_mobile = array_get($params, 'contact_mobile', '');
        $start_time = array_get($params, 'start_time', '');
        $ended_time = array_get($params, 'ended_time', '');
        '' != $telco_id && $telcoes = $telcoes->where('telco_id', $telco_id);
        '' != $contact_mobile && $telcoes = $telcoes->where('contact_mobile', $contact_mobile);
        '' != $start_time && $telcoes = $telcoes->where('assigned_time', '>=', $start_time);
        '' != $ended_time && $telcoes = $telcoes->where('assigned_time', '<=', $ended_time);

        $page  = array_get($params, 'page', 1);
        $limit = array_get($params, 'page_size', 20);
        $list  = $telcoes->paginate($limit, ['*'], 'page', $page)->toArray();
        $values = [
            'list'  => [],
            'total' => $list['total'],
        ];
        foreach ($list['data'] as $telco) {
            $values['list'][] = [
                'id' => $telco['id'],
                'code' => $telco['code'],
                'name' => $telco['name'],
                'assigned_time' => $telco['assigned_time'],
                'contact' => $telco['contact'],
                'contact_mobile' => $telco['contact_mobile'],
                'status' => $telco['status'],
                'levels' => Level::getTelcoLevels($telco['id']),
            ];
        }
        return ResponseService::returnArr($values);
    }

    public static function telcoDetail($params) {
        $telco = self::find($params['id']);
        if ($telco) {
            return [
                'id' => $telco->id,
                'code' => $telco->code,
                'name' => $telco->name,
                'assigned_time' => $telco->assigned_time,
                'contact' => $telco->contact,
                'contact_mobile' => $telco->contact_mobile,
                'status' => $telco->status,
                'province' => $telco->province,
                'city' => $telco->city,
                'district' => $telco->district,
                'address'  => $telco->address,
            ];
        }
        return [];
    }

    public static function loginCreate($params) {
        $user = self::where('code', array_get($params, 'code'))
            ->where('status', '>', ERASE)
            ->first();
        if ($user && $user->status > ERASE) {
            return ResponseService::returnArr([], '用户已存在，请确认', 4000);
        }
        try {
            $user = new self();
            $user->name      = array_get($params, 'code');
            $user->salt      = array_get($params, 'salt');
            $user->password  = array_get($params, 'password');
            $user->desc      = array_get($params, 'role_desc', '');
            $user->status    = array_get($params, 'status');
            $user->real_name = array_get($params, 'name', '');
            $user->save();
            return ResponseService::returnArr();
        } catch (Exception $e) {
            return ResponseService::returnArr([], $e->getMessage(), 4001);
        }
    }
}
