<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\Request;
use think\facade\Db;
use core\base\BaseController;
use core\service\user\UserService;
use app\admin\validate\user\UserValidate;

class UserController extends BaseController
{

    protected array $field = ['mobile', 'password', 'avatar', 'nick_name', 'gender', 'account', 'birthday', 'email', 'status'];

    /**
     * 用户列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        if (($keyword = $request->get('keyword', ''))) {
            if (\tbc\utils\Validate::isChinaMobile($keyword)) {
                $condition['u.mobile'] = $keyword;
            } else if (is_numeric($keyword)) {
                $condition['u.user_id'] = $keyword;
            } else {
                $condition['u.nick_name'] = Db::raw("like '%{$keyword}%'");
            }
        }
        if (($registerTime1 = $request->get('register_time_1', ''))) {
            $condition['create_time'] = Db::raw(" > '$registerTime1'");
        }
        if (($registerTime2 = $request->get('register_time_2', ''))) {
            $condition['create_time'] = Db::raw(" < '$registerTime2'");
        }
        if ($registerTime1 && $registerTime2) {
            $condition['create_time'] =
                Db::raw("between '$registerTime1' and '$registerTime2'");
        }
        $total = UserService::getInstance()->dbQuery()->where($condition)->count();
        $lists = UserService::getInstance()->dbQuery()->where($condition)
            ->order('id', 'desc')
            ->page($this->page(), $this->size())->select()->toArray();
        foreach ($lists as &$item) {
            if ($item['mobile']) {
                $item['mobile'] = substr_replace($item['mobile'], '***', 3, 4);
            }
        }
        return finish(0, '获取成功', ['list' => $lists, 'total' => $total]);
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
        $data['password'] = $data['password'] ?? '';
        validate(UserValidate::class)->scene('edit')->check($data);
//        $this->validate($data, '\app\admin\validate\user\UserValidate.edit');
        UserService::getInstance()->setFormData($data)->addOrUpdate($userId);
        return finish(0, '编辑成功');
    }

    /**
     * 详情
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($userId = $request->get('user_id', ''))) {
            return finish(1, '参数错误');
        }
        $userInfo = UserService::getInstance()->dbQuery()
            ->where('user_id', $userId)->find();
        if (null === $userInfo) return finish(1, '用户不存在或已删除');
        unset($userInfo['password']);
        if ($userInfo['mobile']) {
            $userInfo['mobile'] = substr_replace($userInfo['mobile'], '***', 3, 4);
        }
        return finish(0, '获取成功', $userInfo);
    }

    /**
     * 删除
     * @RequestMapping(path="/delete", methods="get, post, delete")
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
