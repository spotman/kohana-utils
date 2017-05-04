<?php
namespace BetaKiller\Utils\Instance;

use BetaKiller\DI\Container;

/**
 * Use this trait if you need cached instance creator.
 * Override protected constructor if needed.
 * Usage (client-code): CLASS::instance();
 * @deprecated Use NamespaceBasedFactory instead
 */
trait Cached
{
    /**
     * @var static[]
     */
    protected static $_instances;

    /**
     * @return static
     * @deprecated Use NamespaceBasedFactory instead
     */
    public static function getInstance()
    {
        return Container::getInstance()->get(get_called_class());
    }

    final private function __clone() {}
}
