<?php
declare(strict_types=1);

namespace core\base;

class BaseFrontModule
{
    protected array $module = [];

    public function clear(): self
    {
        $this->module = [];
        return $this;
    }

    public function module(): array
    {
        return $this->module;
    }
}