<?php defined('SYSPATH') OR die('No direct script access.');

trait Util_Factory_Cached {

    use Util_Factory;

    protected static $_factory_instances = array();

    /**
     * @param $name
     * @return static
     */
    public static function factory($name)
    {
        // Caching instances
        if ( ! isset(static::$_factory_instances[$name]) )
        {
            $callable = array('static', 'instance_factory');
            static::$_factory_instances[$name] = forward_static_call_array($callable, func_get_args());
        }

        return static::$_factory_instances[$name];
    }

}