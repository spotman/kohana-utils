<?php
namespace BetaKiller\Utils\Instance;

/**
 * Trait BetaKiller_Utils_Instance_Simple
 *
 * Use this trait if you need simple instance creator.
 * Override protected constructor if needed.
 * Usage (client-code): CLASS::instance();
 * @deprecated Bad practice, use DI in constructor instead
 */
trait Simple
{
    /**
     * @return static
     * @deprecated Bad practice, use DI in constructor instead
     */
    public static function instance()
    {
        return new static;
    }
}
