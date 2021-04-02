<?php
declare(strict_types=1);

namespace core\service\order;

use core\base\BaseService;
use think\exception\ValidateException;

class OrderOperationService extends BaseService
{
    protected string $table = 'order_operation';
    protected string $tableUniqueKey = 'operation_id';

    protected function onBeforeInsert()
    {
        if (!$this->formData['user_id'] && !$this->formData['system_user_id']) {
            throw new ValidateException('操作人ID错误');
        }
    }

    public function add($orderSn, $operate, $userId = '', $systemUserId = '')
    {
        $rData = [];
        $rData['order_sn'] = $orderSn;
        $rData['operate'] = $operate;
        $rData['user_id'] = $userId;
        $rData['system_user_id'] = $systemUserId;
        $this->setFormData($rData)->addOrUpdate();
    }
}