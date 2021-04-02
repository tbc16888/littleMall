<?php
declare(strict_types=1);

namespace app\admin\controller\app;

use app\Request;
use core\base\BaseController;
use core\service\app\VersionService;
use app\admin\validate\app\VersionValidate;

class VersionController extends BaseController
{
    protected string $field = 'version_name, version_number, platform, status, is_force_update, download_url, version_desc';

    /**
     * 列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $instance = VersionService::getInstance();
        $total = $instance->dbQuery()->count();
        $lists = $instance->dbQuery()->page($this->page(), $this->size())
            ->order('id', 'desc')->select()->toArray();
        return finish(0, '获取成功', ['list' => $lists,
            'total' => $total]);
    }

    /**
     * 添加
     * @RequestMapping(path="/add", methods="post")
     * @param Request $request
     * @return false|string|void
     * @throws BusinessException
     */
    public function add(Request $request)
    {
        $data = $request->getPostParams($this->field);
        $this->validate($data, VersionValidate::class);
        VersionService::getInstance()->setFormData($data)->addOrUpdate();
        return finish(0, '添加成功');
    }

    /**
     * 编辑
     * @RequestMapping(path="/edit", methods="post")
     * @param Request $request
     * @return false|string|void
     * @throws BusinessException
     */
    public function edit(Request $request)
    {
        if (!($versionId = $request->post('version_id', ''))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams($this->field);
        $this->validate($data, VersionValidate::class);
        VersionService::getInstance()->setFormData($data)->addOrUpdate($versionId);
        return finish(0, '编辑成功');
    }

    /**
     * 删除
     * @RequestMapping(path="/delete", methods="get, post, delete")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if (!($versionId = $request->param('version_id', ''))) {
            return finish(1, '参数错误');
        }
        VersionService::getInstance()->softDelete($versionId);
        return finish(0, '删除成功');
    }
}