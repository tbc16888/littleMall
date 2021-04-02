<?php
declare(strict_types=1);

namespace app\admin\controller\app;

use app\Request;
use core\base\BaseController;
use core\service\app\TemplateService;
use app\admin\validate\app\TemplateValidate;

class TemplateController extends BaseController
{

    protected string $field = 'module, page, status, template_name, sketch, config';

    /**
     * 列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        if (($keyword = $request->get('keyword', ''))) {
            $condition['template_name'] =
                \think\facade\Db::raw("like '%{$keyword}%'");
        }
        $service = TemplateService::getInstance();
        $total = $service->dbQuery($condition)->count();
        $lists = $service->dbQuery($condition)->page($this->page(), $this->size())
            ->order('id', 'desc')->select()->toArray();
        return finish(0, '获取成功', ['list' => $lists,
            'total' => $total]);
    }

    /**
     * 详情
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($templateId = $request->get('template_id', ''))) {
            return finish(1, '参数错误');
        }
        $info = TemplateService::getInstance()->getInfo($templateId);
        if (null === $info) return finish(1, '数据不存在或已删除');
        return finish(0, '获取成功', $info);
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
        $this->validate($data, TemplateValidate::class);
        TemplateService::getInstance()->setFormData($data)->addOrUpdate();
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
        if (!($templateId = $request->post('template_id', ''))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams($this->field);
        $this->validate($data, TemplateValidate::class);
        TemplateService::getInstance()->setFormData($data)->addOrUpdate($templateId);
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
        if (!($templateId = $request->param('template_id', ''))) {
            return finish(1, '参数错误');
        }
        TemplateService::getInstance()->softDelete($templateId);
        return finish(0, '删除成功');
    }

    /**
     * 复制
     * @RequestMapping(path="/copy", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function copy(Request $request)
    {
        if (!($templateId = $request->post('template_id', ''))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams($this->field);
        $this->validate($data, TemplateValidate::class);
        TemplateService::getInstance()->setFormData($data)->addOrUpdate();
        return finish(0, '复制成功');
    }
}