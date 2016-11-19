<?php
namespace BetaKiller\Utils\Instance;

/**
 * Use this trait if you need cached instance creator.
 * Override protected constructor if needed.
 * Usage (client-code): CLASS::instance();
 */
trait Cached
{
    /**
     * @var static[]
     */
    protected static $_instances;

    /**
     * @return static
     */
    public static function instance()
    {
        $class_name = get_called_class();

        if (!isset(static::$_instances[$class_name]))
        {
            // TODO DI
            static::$_instances[$class_name] = new $class_name();
        }

        return static::$_instances[$class_name];
    }

    final private function __clone() {}
}
