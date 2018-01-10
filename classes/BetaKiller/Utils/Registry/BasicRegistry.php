<?php
namespace BetaKiller\Utils\Registry;

/**
 * Class Util_Registry_Base
 */
class BasicRegistry implements \IteratorAggregate
{
    use RegistryTrait;
//    use Utils\Instance\Simple;

    /**
     * Retrieve an external iterator
     *
     * @return \Traversable|mixed[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->registry);
    }
}
