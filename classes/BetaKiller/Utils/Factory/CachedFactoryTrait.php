<?php
namespace BetaKiller\Utils\Factory;

/**
 * Trait Cached
 *
 * @package BetaKiller\Utils\Factory
 * @deprecated use NamespaceBasedFactory instead
 */
trait CachedFactoryTrait {

    use BaseFactoryTrait;

    protected static $_factory_instances_cache = array();

    /**
     * @param $name
     */
    protected function _create($name)
    {
        // Caching instances
        if ( ! isset(static::$_factory_instances_cache[$name]) )
        {
            static::$_factory_instances_cache[$name] = call_user_func_array(array($this, 'instance_factory'), func_get_args());
        }

        return static::$_factory_instances_cache[$name];
    }

}
