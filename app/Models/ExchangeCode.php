<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeCode extends Model
{
    const USED = 1;
    const FOR_USE = 0;
    public static function getExchangeCode($supplier_id, $product_id) {
        $exchange_code = self::where('supplier_id', $supplier_id)
            ->where('product_id', $product_id)
            ->where('status', self::FOR_USE)
            ->lockForUpdate()
            ->first();
        return $exchange_code;
    }
}
