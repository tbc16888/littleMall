<?php
declare (strict_types=1);

namespace core\service\system;

use core\base\BaseService;

class ConfigService extends BaseService
{

    protected string $table = 'system_config';
    protected string $tableUniqueKey = 'config_id';

    public function getConfig(): array
    {
        $configList = $this->dbQuery()->select()->toArray();
        foreach ($configList as &$item) {
            if ($item['options']) $item['options'] = json_decode($item['options'], true);
        }
        return $configList;
    }

    public function setConfig(array $config): bool
    {
        foreach ($config as $code => $value) {
            $rData = [];
            $rData['code'] = $code;
            $rData['value'] = $value;
            $rData['config_id'] = static::buildSnowflakeId();
            if (null === $this->getValue($code)) {
                $this->db()->insert($rData);
            } else {
                $condition = [];
                $condition['code'] = $code;
                $this->dbQuery($condition)->limit(1)->update($rData);
            }
        }
        return true;
    }

    public function getValue(string $code, $default = null)
    {
        $condition = [];
        $condition['code'] = $code;
        $value = $this->dbQuery($condition)->value('value');
        return null === $value ? $default : $value;
    }
}