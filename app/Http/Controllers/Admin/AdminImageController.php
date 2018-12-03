<?php
namespace  App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\OssImgService;
use App\Http\Controllers\Controller;

class AdminImageController extends Controller
{
    public function upload(Request $req) {
        $params = $req->params;
        $image = $params['image'];

        $oss_service = OssImgService::getInstance();
        $res = $oss_service->uploadFile($image['object'], $image['path']);
        return $res;
    }
}
