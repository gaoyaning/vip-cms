<?php
namespace App\Services\ExchangeCodeService;

use App\Models\ExchangeCode;

class ExchangeCodeService {
    public static function getExchangeCode($supplier_id) {
        $exchange_code = getExchangeCode($supplier_id);
        return $exchange_code;
    }
}
