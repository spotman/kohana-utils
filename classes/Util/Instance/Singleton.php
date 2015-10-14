<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Trait Singleton
 *
 * Use this trait if you need Singleton object.
 * Override protected constructor if needed.
 *
 * Usage (client-code): CLASS::instance();
 */
trait Util_Instance_Singleton {

    use \Util_Instance_Simple;

    protected static $instance;

    /**
     * @return static
     */
    public static function instance()
    {
        if ( ! static::$instance )
        {
            static::$instance = new static;
        }
        return static::$instance;
    }

}
