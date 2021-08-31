<?php
declare(strict_types=1);

namespace app\admin\controller\user;

use app\Request;
use core\base\BaseController;
use core\service\user\UserAddressService;
use app\v1\validate\UserAddressValidate;

class AddressController extends BaseController
{
    protected array $field = ['contact', 'contact_number', 'gender', 'province', 'city', 'district', 'address', 'street', 'house_number', 'longitude', 'latitude', 'is_default', 'user_id'];

    /**
     * 列表
     * @RequestMapping(path="/", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        if (($userId = $request->get('user_id', ''))) {
            $condition['user_id'] = $userId;
        }
        $service = UserAddressService::getInstance();
        $total = $service->dbQuery($condition)->count();
        $lists = $service->getList($condition,
            $this->page(), $this->size());
        foreach ($lists as &$item) {
            $item['full_address'] = UserAddressService::getFullAddressField($item,
                ' ');
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
        UserAddressService::getInstance()->setUserId($data['user_id'])
            ->setFormData($data)->addOrUpdate($addressId);
        return finish(0, '操作成功');
    }
}