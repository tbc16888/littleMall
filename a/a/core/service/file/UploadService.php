<?php
declare(strict_types=1);

namespace core\service\file;

use core\base\BaseService;
use core\service\storage\DbCacheService;

class UploadService extends BaseService
{
    protected $qiNiuCallbackUrl = '';

    // 本地上传配置
    public function getLocalConfig(): array
    {
        $rData = [];
        $rData['adapter'] = 'local';
        $rData['upload_token'] = \tbc\utils\Session::$sessionId;
        $rData['upload_url'] = DOMAIN . url('upload/index');
        $rData['upload_base64_url'] = DOMAIN . url('upload/index');
        $rData['upload_form_field'] = 'file';
        $rData['is_compress'] = 1;
        return $rData;
    }

    // 七牛云上传配置
    public function getQiNiuYunUploadConfig(string $callbackUrl, string $bucket): array
    {
        defined('QN_ACCESS_KEY') or define('QN_ACCESS_KEY', env('qn.access_key'));
        defined('QN_SECRET_KEY') or define('QN_SECRET_KEY', env('qn.secret_key'));
        defined('QN_BUCKET') or define('QN_BUCKET', $bucket);


        $this->qiNiuCallbackUrl = $callbackUrl;
        $token = DbCacheService::getInstance()->key(QN_ACCESS_KEY . ':' . QN_BUCKET)->data(function (DbCacheService $dbCached) {
            return (new \EasySwoole\Oss\QiNiu\Auth(QN_ACCESS_KEY, QN_SECRET_KEY))
                ->uploadToken(QN_BUCKET, null, 3600, [
                    'callbackUrl' => $this->qiNiuCallbackUrl,
                    "callbackBody" => json_encode([
                        "name" => "$(fname)",
                        "key" => "$(key)",
                        "size" => "$(fsize)",
                        "w" => "$(imageInfo.width)",
                        "h" => "$(imageInfo.height)",
                        "hash" => "$(etag)",
                        "type" => "$(mimeType)",
                        "directory" => "$(x:directory)"
                    ])
                ]);
        });

        $rData = [];
        $rData['adapter'] = 'qiniu';
        $rData['upload_token'] = $token;
        $rData['upload_url'] = 'https://up-z2.qiniup.com';
        $rData['upload_base64_url'] = 'http://up-z2.qiniu.com/putb64/-1';
        $rData['upload_form_field'] = 'file';
        $rData['is_compress'] = 1;
        return $rData;
    }
}