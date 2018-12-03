<?php
namespace App\Models;

use App\Services\ResponseService;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    public function telco() {
        return $this->belongsTo('\App\Models\Telco');
    }

    public static function getTelcoLevels($telco_id) {
        return self::where('telco_id', $telco_id)
            ->count();
    }

    public static function addLevel($params) {
        $telco_id = $params['telco_id'];
        $level    = $params['level'];
        $desc     = $params['desc'];
        $status   = array_get($params, 'status', ENABLE);
        $rules    = $params['rules'];

        $level = new self();
        $level->telco_id = $telco_id;
        $level->level    = $level;
        $level->rules    = $rules;
        $level->status   = $status;
        $level->save();
        return;
    }

    public static function allLevel($params) {
        $telco_id = array_get($params, 'telco_id', 0);
        $levels = self::where('telco_id', $telco_id)
            ->get();
        $values = [];
        foreach ($levels as $level) {
            $values[] = [
                'name'  => $level->level,
                'value' => $level->id,
            ];
        }
        return $values;
    }

    public static function queryLevels($params) {
        $telco_id = $params['telco_id'];
        $levels = self::where('telco_id', $telco_id)
            ->get();
        $values = [];
        foreach ($levels as $level) {
            $values[] = [
                'id'   => $level->id,
                'name' => $level->level,
                'desc' => $level->desc,
            ];
        }
        return ResponseService::returnArr($values);
    }

    public static function modifyLevel($params) {
        $id = $params['id'];
        $level = self::find($id);

        $telco_id = array_get($params, 'telco_id', '');
        $level    = array_get($params, 'level', '');
        $desc     = array_get($params, 'desc', '');
        $status   = array_get($params, 'status', '');
        $rules    = array_get($params, 'rules', '');

        '' != $telco_id && $level->telco_id = $telco_id;
        '' != $level    && $level->level    = $level;
        '' != $desc     && $level->desc     = $desc;
        '' != $status   && $level->status   = $status;
        '' != $rules    && $level->rules    = $rules;
        $level->save();
        return;
    }

    public static function levelDetail($params) {
        $id = $params['id'];
        $level = self::find($id);
        if (!$level) {
            throw new \Exception('未能获取等级信息', 7000);
        }
        $telco = $level->telco();
        return [
            'telco_code' => $telco->code,
            'telco_name' => $telco->name,
            'level'  => $level->level,
            'desc'   => $level->desc,
            'status' => $level->status,
            'rules'  => $level->rules,
        ];
    }

    public static function deleteLevel($params) {
        $id = $params['id'];
        $level = self::find($id);
        $level->status = ERASE;
        $level->save();
    }
}
