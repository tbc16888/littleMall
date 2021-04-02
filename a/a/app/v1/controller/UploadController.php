<?php
declare (strict_types=1);

namespace app\v1\controller;

use app\Request;
use core\base\BaseController;
use core\service\file\UploadService;
use core\service\file\FileService;

class UploadController extends BaseController
{
    /**
     * 上传配置
     * @RequestMapping(path="/config", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function config(Request $request)
    {
        $bucket = $request->get('bucket', env('qn.bucket'));
        $domain = DOMAIN . '/v1/upload/qiNiuUploadCallback';
        $config = UploadService::getInstance()
            ->getQiNiuYunUploadConfig($domain, $bucket);
        return finish(0, '获取成功', $config);
    }

    /**
     * 上传成功回掉
     * @RequestMapping(path="/uploadCallback", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function qiNiuUploadCallback(Request $request)
    {
        try {
            $domain = env('qn.domain');
            $fileData = json_decode(array_keys($request->param())[0], true);
            FileService::getInstance()->setFormData([
                'file_title' => $fileData['name'],
                'file_cover' => $domain . $fileData['name'],
                'file_type' => FileService::getFileTypeByMime($fileData['type']),
                'file_mime' => $fileData['type'],
                'file_size' => $fileData['size'],
                'file_url' => $domain . $fileData['key'],
                'directory' => $fileData['directory'] ?? ''
            ])->addOrUpdate();
            return finish(0, '上传成功', FileService::getInstance()->getFormData());
        } catch (\Exception $e) {
            return finish(0, '上传成功', FileService::getInstance()->getFormData());
        }
    }
}
