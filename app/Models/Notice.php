<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    public static function getNotice($params) {
        $notices = self::where('status', ENABLE)
            ->get();
        $values = [];
        foreach ($notices as $notice) {
            $values['notices'][] = [
                'message' => $notice->message,
            ];
        }
        return $values;
    }
}
