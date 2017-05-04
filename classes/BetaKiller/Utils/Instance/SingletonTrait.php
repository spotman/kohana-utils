<?php
namespace BetaKiller\Utils\Instance;

use BetaKiller\Utils\Exception;
use BetaKiller\DI\Container;

/**
 * Trait Singleton
 *
 * Use this trait if you need Singleton object.
 * Override protected constructor if needed.
 *
 * Usage (client-code): CLASS::instance();
 */
trait SingletonTrait
{
    protected static $instance;

    /**
     * @return static
     */
    public static function instance()
    {
        if (!static::$instance) {
            static::$instance = Container::getInstance()->get(static::class);
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
            throw new Exception('Duplicate instantiating is not allowed');
        }
    }

    protected function __clone() {}
}
