<?php
declare(strict_types=1);

namespace core\service\system;

use core\base\BaseService;

class OperationService extends BaseService
{

    protected string $table = 'system_operation_log';
    protected string $tableUniqueKey = 'log_id';

    public function getList(array $params = [], int $page = 1, int $size = 10)
    {
        $field = 'sop.log_id, sop.user_id, sop.create_time, sop.ipv4, sop.code';
        $field .= ', su.account, su.nick_name, sm.menu_name, sm.full_menu_name';
        $instance = $this->dbQuery($params, 'sop')->field($field)
            ->leftJoin('system_user su', 'su.user_id = sop.user_id')
            ->leftJoin('system_menu sm', 'sm.module = sop.module and sm.controller = sop.controller and sm.action = sop.action');
        if ($page == 1 && $size == 1) return $instance->field($field . ', sop.*')->find();
        return $instance->page($page, $size)->order('sop.id', 'desc')->select()->toArray();
    }
}