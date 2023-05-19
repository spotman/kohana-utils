<?php
namespace BetaKiller\Utils\Registry;

/**
 * Class BasicRegistry
 *
 * @package BetaKiller\Utils\Registry
 */
class BasicRegistry implements \IteratorAggregate
{
    private $registry = [];

    /**
     * Retrieve an external iterator
     *
     * @return \Traversable|mixed[]
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->registry);
    }

    /**
     * @param string     $key
     * @param mixed      $object
     * @param bool|null  $ignoreDuplicate
     *
     * @return $this
     * @throws RegistryException
     */
    public function set(string $key, $object, ?bool $ignoreDuplicate = null)
    {
        $ignoreDuplicate = $ignoreDuplicate ?? false;

        if (!$ignoreDuplicate && $this->has($key)) {
            throw new RegistryException('Data for :key key already exists', [':key' => $key]);
        }

        $this->registry[$key] = $object;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->has($key)
            ? $this->registry[$key]
            : null;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->registry = [];

        return $this;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->registry;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        return isset($this->registry[$key]);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return \count($this->registry);
    }

    /**
     * Returns keys of currently added items
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->registry);
    }
}
