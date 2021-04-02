<?php
declare (strict_types=1);

namespace core\base;

use think\facade\Db;
use core\exception\BusinessException;
use Hyperf\Snowflake\Configuration;
use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;
use Hyperf\Snowflake\MetaGenerator\RandomMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;
use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisSecondMetaGenerator;

abstract class BaseService
{
    use \core\base\Singleton;

    protected string $table = '';
    protected string $tableUniqueKey = '';
    protected string $tableUniqueId = '';
    protected string $tableAlias = '';
    protected string $userId = '';
    protected string $adminId = '';
    protected array $formData = [];

    public function setTableUniqueId($tableUniqueId): self
    {
        $this->tableUniqueId = $tableUniqueId;
        return $this;
    }

    public function getTableUniqueId(): string
    {
        return $this->tableUniqueId;
    }

    public function setUserId($userId): self
    {
        $this->userId = strval($userId);
        return $this;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setAdminId($adminId): self
    {
        $this->adminId = strval($adminId);
        return $this;
    }

    public function getAdminId(): string
    {
        return $this->adminId;
    }

    // 生成雪花ID  $index => 循环可能出现的重复
    public static function buildSnowflakeId($index = 0): int
    {
        // 1577808000 => 20200101
        $config = new Configuration();
        $metaGenerator = new RandomMilliSecondMetaGenerator($config,
            1577808000 + $index);
        $metaGenerator->generate()->setSequence(0);
        $generator = new SnowflakeIdGenerator($metaGenerator);
        return $generator->generate();
    }

    public function setFormData(array $form = []): self
    {
        $this->formData = $form;
        return $this;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function db($table = ''): \think\db\Query
    {
        return Db::name($table ? $table : $this->table);
    }

    public function dbQuery(array $condition = [], string $alias = ''): \think\db\Query
    {
        $instance = $this->db()->where($condition);
        $softDelete = 'is_delete';
        if (!$alias) $alias = $this->tableAlias;
        if ($alias) $instance->alias($alias);
        if ($alias) $softDelete = $alias . '.' . $softDelete;
        return $instance->where($softDelete, 0);
    }

    public static function transaction(callable $function)
    {
        return Db::transaction(function () use ($function) {
            return $function();
        });
    }

    protected function listenEvent($event): ?bool
    {
        if (method_exists($this, $event)) return call_user_func([$this, $event]);
        return true;
    }

    public function addOrUpdate($tableUniqueId = ''): self
    {
        if ($tableUniqueId) {
            $this->setTableUniqueId($tableUniqueId);
            $this->listenEvent('onBeforeUpdate');
            $this->listenEvent('onBeforeExecute');
            $condition = [];
            $condition[$this->tableUniqueKey] = $this->tableUniqueId;
            $result = $this->db()->where($condition)->update($this->formData);
            if (false === $result) throw new BusinessException('编辑失败', 1);
        } else {
            $this->listenEvent('onBeforeInsert');
            $this->listenEvent('onBeforeExecute');
            $this->formData['create_time'] = date('Y-m-d H:i:s');
            $this->formData[$this->tableUniqueKey] = static::buildSnowflakeId();
            if (!$this->db()->insert($this->formData)) {
                throw new BusinessException('添加失败', 1);
            }
            $this->setTableUniqueId(strval($this->formData[$this->tableUniqueKey]));
        }
        return $this;
    }

    // 软删除
    public function softDelete($id): self
    {
        $result = $this->db()->where($this->tableUniqueKey, $id)
            ->limit(1)->update(['is_delete' => 1]);
        if (!$result) throw new BusinessException('删除失败');
        return $this;
    }

    public function getInfo($tableUniqueId): ?array
    {
        return $this->dbQuery()->where($this->tableUniqueKey, $tableUniqueId)->find();
    }
}