<?php
namespace BetaKiller\Utils\Instance;

use \BetaKiller\Utils;

/**
 * Trait Singleton
 *
 * Use this trait if you need Singleton object.
 * Override protected constructor if needed.
 *
 * Usage (client-code): CLASS::instance();
 */
trait Singleton {

//    use Utils\Instance\Simple;

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

    /**
     * You can`t create objects directly, use CLASS::instance() instead
     * Also you can define your own protected constructor in child class
     */
    protected function __construct() {}

    protected function __clone() {}

    protected function __wakeup() {}

}
