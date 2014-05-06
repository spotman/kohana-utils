<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Trait Singleton
 *
 * Use this trait if you need Singleton object.
 * Override protected constructor if needed.
 *
 * Usage (client-code): CLASS::instance();
 */
trait Util_Singleton {

    protected static $instance;

    /**
     * @return static
     */
    public static function instance()
    {
        if ( ! static::$instance )
        {
            $class = __CLASS__;
            static::$instance = new $class;
        }
        return static::$instance;
    }

    /**
     * You can`t create Singleton objects directly, use CLASS::instance() instead
     * Also you can define your own protected constructor in child class
     */
    protected function __construct() {}

    protected function __clone() {}

}
