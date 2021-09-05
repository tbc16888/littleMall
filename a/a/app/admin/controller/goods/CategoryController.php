<?php
declare (strict_types=1);

namespace app\admin\controller\goods;

use app\Request;
use core\base\BaseController;
use core\service\goods\GoodsCategoryService;
use app\admin\validate\goods\GoodsCategoryValidate;
use core\service\goods\GoodsModelService;

class CategoryController extends BaseController
{
    protected string $field = 'cat_id, cat_name, cat_path, icon, parent_id, 
    sort_weight, status, create_time, model_id';

    /**
     * 列表
     * @RequestMapping(path="/list", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function index(Request $request)
    {
        $instance = GoodsCategoryService::getInstance()->dbQuery();
        $instance->where('parent_id', $request->get('pid', ''));
        $lists = $instance->field($this->field)->page(1, $this->size())
            ->order('sort_weight', 'desc')->order('id asc')
            ->select()->toArray();
        $total = $instance->count();
        foreach ($lists as &$cat) {
            $cat['label'] = $cat['cat_name'];
            $cat['value'] = $cat['cat_id'];
            $cat['model_name'] = $cat['model_id'] ?
                GoodsModelService::getInstance()->db()->where('model_id', $cat['model_id'])->value('model_name') : '';

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
        $this->validate($data, GoodsCategoryValidate::class);
        $service = GoodsCategoryService::getInstance();
        $catId = $service->setFormData($data)->addOrUpdate()->getTableUniqueId();
        $service->updateChildrenTotal($data['parent_id']);
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
        if (!($catId = $request->post('cat_id', ''))) {
            return finish(1, '参数错误');
        }
        $data = $request->getPostParams($this->field);
        $this->validate($data, GoodsCategoryValidate::class);
        GoodsCategoryService::getInstance()->setFormData($data)->addOrUpdate($catId);
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
        if (!($catId = $request->param('cat_id', ''))) {
            return finish(1, '参数错误');
        }
        GoodsCategoryService::getInstance()->softDelete($catId);
        return finish(0, '删除成功');
    }

    /**
     * 设置关联模型
     * @RequestMapping(path="/setModel", methods="post")
     * @param Request $request
     * @return false|string|void
     */
    public function setModel(Request $request)
    {
        if (!($catId = $request->post('cat_id', ''))) {
            return finish(1, '参数错误');
        }
        $modelId = $request->post('model_id', '');
        $isSyncChildren = $request->post('is_sync_children', 0);
        $isSyncChildren = boolval($isSyncChildren);
        GoodsCategoryService::getInstance()->setModel($catId, $modelId,
            $isSyncChildren);
        return finish(0, '设置成功');
    }

    public function async(Request $request)
    {
        $condition = [];
        $condition['parent_id'] = $request->get('pid', '');
        $service = new GoodsCategoryService();
        $field = 'cat_id as value, cat_name as label, children as leaf, model_id';
        $lists = $service->dbQuery($condition)->field($field)
            ->page($this->page(), $this->size())
            ->order('sort_weight', 'desc')->order('id asc')
            ->select()->toArray();
        return finish(0, '获取成功', ['list' => $lists,
            'total' => count($lists)]);
    }
}
