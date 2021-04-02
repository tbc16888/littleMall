<?php
declare(strict_types=1);

namespace app\admin\controller\system;

use app\Request;
use core\base\BaseController;
use app\admin\validate\system\UserValidate;
use core\service\system\OperationService;
use core\service\system\RoleService;
use core\service\system\UserService;

class UserController extends BaseController
{

    protected string $field = 'account, password, status, role_id, nick_name, create_time, user_id';

    /**
     * 列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $instance = UserService::getInstance()->dbQuery();
        $lists = $instance->field($this->field)->page($this->page(), $this->size())
            ->order('id', 'desc')->select()->toArray();
        $total = $instance->count();
        foreach ($lists as &$item) {
            $item['role_name'] = '';
            if ($item['role_id']) $item['role_name'] = RoleService::getInstance()->dbQuery()->where('role_id', $item['role_id'])->value('role_name');
            unset($item['password']);
            $login = OperationService::getInstance()->db()
                ->where('user_id', $item['user_id'])
                ->where('controller', 'login')
                ->where('action', 'index')
                ->order('id', 'desc')->find();
            if (null === $login) continue;
            $item['login_ip'] = $login['ipv4'];
            $item['login_time'] = $login['create_time'];
        }
        return finish(0, '获取成功', ['list' => $lists,
            'total' => $total]);
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
        $this->validate($data, UserValidate::class);
        UserService::getInstance()->setFormData($data)->addOrUpdate();
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
        if (!($userId = $request->post('user_id', ''))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams($this->field);
        $this->validate($data, UserValidate::class);
        UserService::getInstance()->setFormData(array_filter($data))
            ->addOrUpdate($userId);
        return finish(0, '编辑成功');
    }

    /**
     * 删除
     * @RequestMapping(path="/delete", methods="post,delete,get")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if (!($userId = $request->param('user_id', ''))) {
            return finish(1, '参数错误');
        }
        UserService::getInstance()->softDelete($userId);
        return finish(0, '删除成功');
    }
}