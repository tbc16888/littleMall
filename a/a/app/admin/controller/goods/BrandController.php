<?php
declare (strict_types=1);

namespace app\admin\controller\goods;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsBrandService;
use app\admin\validate\goods\GoodsBrandValidate;

class BrandController extends BaseController
{

    protected string $field = 'brand_id, brand_name, sort_weight, brand_site, brand_desc, first_letter, brand_logo, status, create_time';

    /**
     * 列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $condition = [];
        if (($keyword = $request->get('keyword', '', 'trim'))) {
            $condition['brand_name'] =
                \think\facade\Db::raw("like '%{$keyword}%'");
        }
        $service = GoodsBrandService::getInstance();
        $total = $service->dbQuery($condition)->count();
        $lists = $service->dbQuery($condition)->field($this->field)
            ->page($this->page(), $this->size())
            ->order('id', 'desc')->select()->toArray();
        return finish(0, '获取成功', ['total' => $total,
            'list' => $lists]);
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
        $this->validate($data, GoodsBrandValidate::class);
        GoodsBrandService::getInstance()->setFormData($data)->addOrUpdate();
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
        if (!($brandId = $request->post('brand_id', ''))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams($this->field);
        $this->validate($data, GoodsBrandValidate::class);
        GoodsBrandService::getInstance()->setFormData($data)->addOrUpdate($brandId);
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
        if (!($brandId = $request->param('brand_id'))) {
            return finish(1, '参数错误');
        }
        GoodsBrandService::getInstance()->softDelete($brandId);
        return finish(0, '删除成功');
    }
}
