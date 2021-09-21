<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\Request;
use think\facade\Db;
use core\base\BaseController;
use core\service\file\FileService;
use app\admin\validate\file\FileValidate;

class FileController extends BaseController
{

    protected array $field = [
        'file_title', 'file_url', 'file_type', 'is_directory', 'directory', 'file_mime'
    ];


    /**
     * 列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        $condition['directory'] = $request->get('directory', '');
        if (($keyword = $request->get('keyword', ''))) {
            $condition['file_title'] = Db::raw("like '%{$keyword}%'");
        }
        $total = FileService::getInstance()->dbQuery($condition)->count();
        $lists = FileService::getInstance()->dbQuery($condition)
            ->order('is_directory desc, create_time desc')
            ->page($this->page(), $this->size())->select()->toArray();
        foreach ($lists as &$file) {
            $file['file_cover'] = FileService::getCover($file);
        }
        return finish(0, '获取成功', ['total' => $total, 'list' => $lists]);
    }

    /**
     * 添加
     * @RequestMapping(path="/add", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function add(Request $request)
    {
        $data = $request->getPostParams($this->field);
        $rule = FileValidate::class;
        if ($data['is_directory'] ?? 0) $rule = $rule . '.directory';
        $this->validate($data, $rule);
        FileService::getInstance()->setFormData($data)->addOrUpdate();
        return finish(0, '添加成功');
    }

    /**
     * 编辑
     * @RequestMapping(path="/edit", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function edit(Request $request)
    {
        if (!($fileId = $request->post('file_id', ''))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams($this->field);
        $rule = FileValidate::class;
        if ($data['is_directory'] ?? 0) $rule = $rule . '.directory';
        $this->validate($data, $rule);
        FileService::getInstance()->setFormData($data)->addOrUpdate();
        return finish(0, '编辑成功');
    }

    /**
     * 删除文件
     * @RequestMapping(path="/delete", methods="get, post, delete")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if (!($fileId = $request->param('file_id', ''))) {
            return finish(1, '参数错误');
        }
        FileService::transaction(function () use ($fileId) {
            FileService::getInstance()->softDelete($fileId);
            FileService::getInstance()->moveToDirectory($fileId, '');
        });
        return finish(0, '删除成功');
    }

    /**
     * 目录树
     * @RequestMapping(path="/tree", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function tree(Request $request)
    {
        $treeList = FileService::getInstance()->getDirectoryTree('');
        return finish(0, '操作成功', ['list' => $treeList]);
    }

    /**
     * 移动文件到目录
     * @RequestMapping(path="/move", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function move(Request $request)
    {
        if (!($fileId = $request->param('file_id', ''))) {
            return finish(1, '参数错误');
        }
        $directoryId = $request->param('directory_id', '');
        FileService::getInstance()->moveToDirectory($fileId, $directoryId);
        return finish(0, '操作成功');
    }

    /**
     * 获取网络文件信息
     * @RequestMapping(path="/getNetworkFileInfo", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function getNetworkFileInfo(Request $request)
    {
        if (!($fileUrl = $request->param('file_url', ''))) {
            return finish(1, '参数错误');
        }
        $client = new \GuzzleHttp\Client();
        $result = $client->get($fileUrl);
        $contentType = $result->getHeader('content-type') ?? [''];
        $fileInfo = [];
        $fileInfo['file_mime'] = $contentType[0];
        $fileInfo['file_type'] = FileService::getFileTypeByMime($fileInfo['file_mime']);
        $fileName = explode('/', $fileUrl);
        $fileInfo['file_title'] = end($fileName);
        $fileInfo['file_url'] = $fileUrl;
        return finish(0, '获取成功', $fileInfo);
    }

}
