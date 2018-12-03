<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipUser extends Model
{
    public static function addOrFindUser($mobile) {
        $VipUser = self::where('mobile', $mobile)
            ->first();
        if (!$VipUser) {
            $VipUser = new self();
            $VipUser->mobile = $mobile;
            $VipUser->save();
        }
        return $VipUser;
    } 
}
