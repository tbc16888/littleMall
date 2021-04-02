<?php
declare (strict_types=1);

namespace app\admin\controller\system;

use app\Request;
use core\base\BaseController;
use app\admin\validate\system\RoleValidate;
use core\service\system\RoleService;

class RoleController extends BaseController
{
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
            $condition['role_name'] =
                \think\facade\Db::raw("like '%{$keyword}%'");
        }
        $service = RoleService::getInstance();
        $total = $service->dbQuery($condition)->count();
        $lists = $service->dbQuery($condition)->page($this->page(), $this->size())
            ->order('id', 'desc')->select()->toArray();
        return finish(0, '获取成功', ['total' => $total,
            'list' => $lists]);
    }

    /**
     * 详情
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($roleId = $request->get('role_id', ''))) {
            return finish(1, '参数错误');
        }

        $roleInfo = RoleService::getInstance()->getInfo($roleId);

        if (null === $roleInfo) return finish(1, '角色不存在');
        $roleInfo['menu_code'] = RoleService::getInstance()->getMenu($roleId, 'menu_code');
        return finish(0, '获取成功', $roleInfo);
    }

    /**
     * 添加
     * @RequestMapping(path="/add", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function add(Request $request)
    {
        $data = $request->getPostParams('role_name, status, role_desc');
        $this->validate($data, RoleValidate::class);
        RoleService::transaction(function () use ($data) {
            RoleService::getInstance()->setFormData($data)->addOrUpdate()
                ->setMenu($this->request->post('menu/a', []));
        });
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
        if (!($roleId = $request->post('role_id', ''))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams('role_name, status, role_desc');
        $this->validate($data, RoleValidate::class);
        RoleService::transaction(function () use ($roleId, $data) {
            RoleService::getInstance()->setFormData($data)->addOrUpdate($roleId)
                ->setMenu($this->request->post('menu/a', []));
        });
        return finish(0, '编辑成功');
    }

    /**
     * 删除
     * @RequestMapping(path="/delete", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if (!($roleId = $request->param('role_id', ''))) {
            return finish(1, '参数错误');
        }
        RoleService::getInstance()->softDelete($roleId);
        return finish(0, '删除成功');
    }
}

