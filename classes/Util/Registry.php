<?php defined('SYSPATH') OR die('No direct script access');

class Util_Registry
{
    static protected $_instance = NULL;

    protected $_registry = array();

    /**
     * @return static
     */
    static public function instance()
    {
        if ( static::$_instance === NULL )
        {
            static::$_instance = new static;
        }

        return static::$_instance;
    }

    static public function set($key, $object)
    {
        static::instance()->_registry[$key] = $object;
    }

    static public function get($key)
    {
        if ( isset(static::instance()->_registry[$key]) )
        {
            return static::instance()->_registry[$key];
        }
    }

    public function __isset($key)
    {
        return isset(static::instance()->_registry[$key]);
    }

    public function __get($key)
    {
        return static::instance()->get($key);
    }

    public function __set($key, $object)
    {
        static::instance()->set($key, $object);
    }

    private function __wakeup()
    {

    }

    private function __construct()
    {

    }

    private function __clone()
    {

    }
}