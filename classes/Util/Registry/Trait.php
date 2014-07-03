<?php defined('SYSPATH') OR die('No direct script access');

trait Util_Registry_Trait
{
    protected $_registry = array();

    public function set($key, $object)
    {
        if ( $this->__isset($key) )
            throw new Kohana_Exception('Data for :key key already exists', array(':key' => $key));

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