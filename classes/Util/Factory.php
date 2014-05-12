<?php defined('SYSPATH') OR die('No direct script access.');

trait Util_Factory {

    /**
     * @param $name
     * @return static
     * @throws HTTP_Exception_500
     */
    public static function factory($name)
    {
        return forward_static_call_array(array('static', 'instance_factory'), func_get_args());
    }

    protected static function instance_factory($name)
    {
        $class_name = static::make_instance_class_name($name);

        if ( ! class_exists($class_name) )
            throw new HTTP_Exception_500('Class :class is absent', array(':class' => $class_name));

        $args = array_merge(array($class_name), func_get_args());

        return forward_static_call_array(array('static', 'make_instance'), $args);
    }

    protected static function make_instance_class_name($name)
    {
        return __CLASS__.'_'.$name;
    }

    protected static function make_instance($class_name)
    {
        return new $class_name;
    }

}