<?php
namespace BetaKiller\Utils\Factory;

trait Base {

    /**
     * Factory method
     *
     * @param $name
     * @return static
     */
    public function create($name)
    {
        return $this->_create($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function _create($name) // , ...$params
    {
        return call_user_func_array(array($this, 'instance_factory'), func_get_args());
    }

    protected function instance_factory($name)
    {
        $class_names = $this->make_instance_class_name($name);

        if ( !is_array($class_names) )
            $class_names = array($class_names);

        $class_name = NULL;

        foreach ($class_names as $class_names_item) {
            if ( class_exists($class_names_item) ) {
                $class_name = $class_names_item;
                break;
            }
        }

        if ( !$class_name )
            throw new Exception\Missing('Can not factory :name in :class',
                array(':name' => $name, ':class' => get_class($this)));

        $args = func_get_args();
        array_shift($args);   // Remove codename
        $args = array_merge(array($class_name), $args); // Add class name

        $instance = call_user_func_array(array($this, 'make_instance'), $args);

        $this->store_codename($instance, $name);

        return $instance;
    }

    protected function store_codename($instance, $codename)
    {
        // Empty by default
        // Use this method to save original factory codename
    }

    protected function make_instance_class_name($name)
    {
        return '\\'.__CLASS__.'_'.$name;
    }

    protected function make_instance($class_name)
    {
        return new $class_name;
    }

}
