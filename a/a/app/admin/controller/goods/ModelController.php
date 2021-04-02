<?php
declare (strict_types=1);

namespace app\admin\controller\goods;

use app\Request;
use app\admin\validate\goods\GoodsModelValidate;
use core\base\BaseController;
use core\service\goods\GoodsModelService;
use core\service\goods\GoodsModelAttributeService;

class ModelController extends BaseController
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
            $condition['model_name'] =
                \think\facade\Db::raw("like '%{$keyword}%'");
        }
        $service = GoodsModelService::getInstance();
        $total = $service->dbQuery($condition)->count();
        $lists = $service->dbQuery($condition)->page($this->page(), $this->size())
            ->select()->toArray();
        return finish(0, '获取成功', ['total' => $total,
            'list' => $lists]);
    }

    /**
     * 列表
     * @RequestMapping(path="/detail", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function detail(Request $request)
    {
        if (!($modelId = $request->get('model_id', ''))) {
            return finish(1, '参数错误');
        }
        $field = 'model_name, model_id, status, sort_weight';
        $info = GoodsModelService::getInstance()->dbQuery()->field($field)
            ->where('model_id', $modelId)->find();
        $attribute = GoodsModelAttributeService::getInstance()->dbQuery()
            ->where('model_id', $modelId)->select()->toArray();
        $info['attribute'] = $attribute;
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
        $field = 'model_name, sort_weight, status';
        $data = $this->request->getPostParams($field);
        $this->validate($data, GoodsModelValidate::class);
        GoodsModelService::transaction(function () use ($data) {
            $modelId = GoodsModelService::getInstance()->setFormData($data)
                ->addOrUpdate()->getTableUniqueId();
            $attribute = $this->request->post('attribute', '[]');
            $attribute = json_decode($attribute, true);
            GoodsModelAttributeService::getInstance()->setAttribute($modelId,
                $attribute);
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
        if (!($modelId = $request->post('model_id', ''))) {
            return finish(1, '参数错误');
        }
        $field = 'model_name, sort_weight, status';
        $data = $this->request->getPostParams($field);
        $this->validate($data, GoodsModelValidate::class);
        GoodsModelService::transaction(function () use ($data, $modelId) {
            $modelId = GoodsModelService::getInstance()->setFormData($data)
                ->addOrUpdate($modelId)->getTableUniqueId();
            $attribute = $this->request->post('attribute', '[]');
            $attribute = json_decode($attribute, true);
            GoodsModelAttributeService::getInstance()->setAttribute($modelId,
                $attribute);
        });
        return finish(0, '编辑成功');
    }

    /**
     * 删除
     * @RequestMapping(path="/delete", methods="any")
     * @param Request $request
     * @return false|string|void
     */
    public function delete(Request $request)
    {
        if (!($modelId = $request->param('model_id'))) {
            return finish(1, '参数错误');
        }
        GoodsModelService::getInstance()->softDelete($modelId);
        return finish(0, '删除成功');
    }

    /**
     * 分类属性
     * @RequestMapping(path="/attribute", methods="get")
     * @param Request $request
     * @return false|string|void
     */
    public function attribute(Request $request)
    {
        if (!($modelId = $request->get('model_id', ''))) {
            return finish(1, '参数错误');
        }
        $allAttribute = GoodsModelAttributeService::getInstance()
            ->dbQuery()->where('model_id', $modelId)->select()->toArray();
        $attribute = ['base' => [], 'spec' => []];
        foreach ($allAttribute as $key => $item) {
            $options = array_filter(explode("\n", $item['options']));
            $item['attr_values'] = [];
            foreach ($options as $val) {
                $item['attr_values'][] = [
                    'attr_id' => $item['attr_id'], 'value' => $val,
                    'label' => $val, 'remark' => ''];
            }
            if ($item['attr_type'] == 1) $attribute['base'][] = $item;
            if ($item['attr_type'] == 2) $attribute['spec'][] = $item;
        }
        return finish(0, '获取成功', $attribute);
    }
}
