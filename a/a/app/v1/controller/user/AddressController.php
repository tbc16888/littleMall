<?php
declare (strict_types=1);

namespace app\v1\controller\user;

use app\Request;
use core\base\BaseController;
use app\v1\validate\UserAddressValidate;
use core\service\user\UserAddressService;

class AddressController extends BaseController
{
    // 权限认证
    protected array $middleware = [
        \app\v1\middleware\Auth::class
    ];

    protected array $field = ['contact', 'contact_number', 'gender', 'province', 'city', 'district', 'address', 'street', 'house_number', 'longitude', 'latitude', 'is_default', 'user_id'];

    public function initialize()
    {
        UserAddressService::getInstance()->setUserId(session('user_id'));
    }

    /**
     * 列表
     * @RequestMapping(path="/", methods="get, post, delete")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $service = UserAddressService::getInstance();
        $condition = [];
        $condition['ua.user_id'] = UserAddressService::getInstance()
            ->getUserId();
        $total = $service->dbQuery($condition, 'ua')->count();
        $lists = $service->getList($condition, $this->page(), $this->size());
        foreach ($lists as &$item) {
            $item['full_address'] = UserAddressService::getFullAddressField($item);
        }
        return finish(0, '获取成功', ['total' => $total,
            'list' => $lists]);
    }

    /**
     * 列表
     * @RequestMapping(path="/detail", methods="get, post, delete")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($addressId = $request->get('address_id', ''))) {
            return finish(1, '参数错误');
        }
        $address = UserAddressService::getInstance()->getInfo($addressId);
        if (null === $address) return finish(1, '数据不存在或已删除');
        return finish(0, '获取成功', $address);
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
        $this->validate($data, UserAddressValidate::class);
        UserAddressService::getInstance()->setFormData($data)->addOrUpdate();
        return finish(0, '操作成功');
    }

    /**
     * 编辑
     * @RequestMapping(path="/edit", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function edit(Request $request)
    {
        if (!($addressId = $request->post('address_id', ''))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams($this->field);
        $this->validate($data, UserAddressValidate::class);
        UserAddressService::getInstance()->setFormData($data)
            ->addOrUpdate($addressId);
        return finish(0, '操作成功');
    }

    /**
     * 删除
     * @RequestMapping(path="/delete", methods="any")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if (!($addressId = $request->param('address_id', ''))) {
            return finish(1, '参数错误');
        }
        UserAddressService::getInstance()->softDelete($addressId);
        return finish(0, '操作成功');
    }
}
