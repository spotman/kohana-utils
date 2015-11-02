<?php
namespace BetaKiller\Utils\Registry;

use BetaKiller\Utils;
use Traversable;

/**
 * Class Util_Registry_Base
 */
class Base implements \IteratorAggregate
{
    use Utils\Registry,
        Utils\Instance\Simple;

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *        <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_registry);
    }
}
