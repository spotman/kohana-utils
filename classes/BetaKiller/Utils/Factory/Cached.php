<?php
namespace BetaKiller\Utils\Factory;

trait Cached {

    use Base;

    protected static $_factory_instances_cache = array();

    /**
     * @param $name
     * @return static
     */
    protected function _create($name)
    {
        // Caching instances
        if ( ! isset(static::$_factory_instances_cache[$name]) )
        {
            $callable = array($this, 'instance_factory');
            static::$_factory_instances_cache[$name] = call_user_func_array($callable, func_get_args());
        }

        return static::$_factory_instances_cache[$name];
    }

}
