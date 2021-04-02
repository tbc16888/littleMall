<?php
declare (strict_types=1);

namespace core\base;

trait Singleton
{
    protected static $instances = [];

    /**
     * @param mixed ...$args
     * @return static
     */
    public static function getInstance(...$args)
    {
        $class = get_called_class();
        if (!isset(static::$instances[$class])) {
            static::$instances[$class] = new static(...$args);
        }
        return static::$instances[$class];
    }
}