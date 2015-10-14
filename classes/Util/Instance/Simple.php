<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Trait Util_Instance_Simple
 *
 * Use this trait if you need simple instance creator.
 * Override protected constructor if needed.
 * Usage (client-code): CLASS::instance();
 */
trait Util_Instance_Simple {

    public static function instance()
    {
        return new static;
    }

    /**
     * You can`t create objects directly, use CLASS::instance() instead
     * Also you can define your own protected constructor in child class
     */
    protected function __construct() {}

    protected function __clone() {}

    protected function __wakeup() {}

}
