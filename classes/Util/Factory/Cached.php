<?php defined('SYSPATH') OR die('No direct script access.');

trait Util_Factory_Cached {

    use Util_Factory;

    protected static $_factory_instances_cache = array();

    /**
     * @param $name
     * @return static
     */
    public function create($name)
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
