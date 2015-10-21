<?php
namespace BetaKiller\Utils;

use \BetaKiller\Utils\Registry\Exception;

trait Registry
{
    protected $_registry = array();

    /**
     * @param string $key
     * @param mixed $object
     * @param bool|FALSE $ignore_duplicate
     * @return $this
     * @throws Exception
     */
    public function set($key, $object, $ignore_duplicate = FALSE)
    {
        if ( !$ignore_duplicate AND $this->has($key) )
            throw new Exception('Data for :key key already exists', array(':key' => $key));

        $this->_registry[$key] = $object;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->has($key)
            ? $this->_registry[$key]
            : NULL;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->_registry = array();
        return $this;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->_registry;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->_registry[$key]);
    }

    /**
     * @param $key
     * @return bool
     * @deprecated
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * @param $key
     * @return null
     * @deprecated
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @param $key
     * @param $object
     * @deprecated
     * @throws Exception
     */
    public function __set($key, $object)
    {
        $this->set($key, $object);
    }

}
