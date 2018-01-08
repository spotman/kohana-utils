<?php
namespace BetaKiller\Utils\Instance;

use BetaKiller\Utils\UtilsException;

/**
 * Trait Singleton
 *
 * Use this trait if you need Singleton object.
 * Override protected constructor if needed.
 *
 * Usage (client-code): CLASS::instance();
 *
 * @deprecated Use DI instead
 */
trait SingletonTrait
{
    protected static $instance;

    /**
     * Use DI instead of calling ::instance()
     *
     * @return static
     * @deprecated
     */
    public static function instance()
    {
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * You can`t create objects directly, use CLASS::instance() instead
     * Also you can define your own protected constructor in child class
     */
    public function __construct()
    {
        if (static::$instance) {
            throw new UtilsException('Duplicate instantiating is not allowed');
        }
    }

    protected function __clone() {}
}
