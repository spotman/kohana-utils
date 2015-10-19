<?php
namespace BetaKiller\Utils;

use \BetaKiller\Utils\Registry\Exception;

trait Registry
{
    protected $_registry = array();

    public function set($key, $object, $ignore_duplicate = FALSE)
    {
        if ( !$ignore_duplicate AND $this->__isset($key) )
            throw new Exception('Data for :key key already exists', array(':key' => $key));

        $this->_registry[$key] = $object;
        return $this;
    }

    public function get($key)
    {
        return $this->__isset($key)
            ? $this->_registry[$key]
            : NULL;
    }

    public function clear()
    {
        $this->_registry = array();
        return $this;
    }

    public function get_all()
    {
        return $this->_registry;
    }

    public function is_set($key)
    {
        return isset($this->_registry[$key]);
    }

    public function __isset($key)
    {
        return isset($this->_registry[$key]);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $object)
    {
        $this->set($key, $object);
    }

}
