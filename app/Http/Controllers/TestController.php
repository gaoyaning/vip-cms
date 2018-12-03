<?php
namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Controllers\Controller;
use App\Services\Redis;

class TestController extends Controller
{
    public function test() {
        Redis::set('gyning.kxb', 'love');
        \Log::debug('----------', [Redis::get('gyning.kxb')]);
        return Permission::where('uri_path', '!=', '')
        ->paginate(5, ['name', 'uri_path'], 'page', 2);
    }
}
