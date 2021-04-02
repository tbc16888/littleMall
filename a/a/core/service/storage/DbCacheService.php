<?php
declare(strict_types=1);

namespace core\service\storage;

use core\base\BaseService;

class DbCacheService extends BaseService
{

    protected string $table = 'storage';
    protected string $tableUniqueKey = 'storage_id';

    protected string $key = '';

    public function key($key): self
    {
        $this->key = $key;
        return $this;
    }

    // 设置
    public function set(string $key, $value = '', int $expire = 3600): self
    {
        $rData = [];
        $rData['storage_id'] = static::buildSnowflakeId();
        $rData['key'] = $key;
        $rData['data'] = $value;
        $rData['expire'] = time() + 3600;
        $this->db()->insert($rData);
        return $this;
    }

    // 获取
    public function get(string $key, $default = null)
    {
        $data = $this->db()->where('key', $key)->whereRaw('expire > ?', [time()])
            ->order('id', 'desc')->find();
        if (null === $data && is_callable($default)) {
            $data = $default($this);
            $this->set($key, $data);
            return $data;
        }
        if (null === $data) return $default;
        return $data['data'];
    }

    public function data($default = null)
    {
        return $this->get($this->key, $default);
    }
}