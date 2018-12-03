<?php
namespace App\Services;

use OSS\OssClient;
use OSS\Core\OssException;

class OssImgService {
    public static $instance = null;
    private $oss = null;
    public $accessKeyId = 'iazHySmLjDuwjpTH';
    public $accessKeySecret = 'tQJpZjeln7lrGAgkZ6THjCVcqJmze7';
    public $endpoint = 'http://oss-cn-qingdao.aliyuncs.com';
    public $bucket = 'ofo';

    public function __construct() {
        try {
            $this->oss = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
        } catch (\Exception $e) {
            \Log::error("初始化OSS服务异常", ['code' => 4009, 'message' => $e->getMessage()]);
            throw new \Exception("初始化OSS服务异常", 4009);
        }
    }

    public static function getInstance() {
        if (self::$instance) {
            return self::$instance;
        } else {
            self::$instance = new OssImgService();
            return self::$instance;
        }
    }

    public function uploadFile($object, $content) {
        try {
            $res = $this->oss->uploadFile($this->bucket, $object, $content, []);
            return [
                'code' => 200,
                'message' => '',
                'values' => [
                    'url' => array_get($res, 'info.url', ''),
                ],
            ];
        } catch (\Exception $e) {
            \Log::notice("上传图片失败", ['code' => $e->getCode(), 'message' => $e->getMessage()]);
            return [
                'code' => 4010,
                'message' => '上传图片失败',
            ];
        }
    }
}
