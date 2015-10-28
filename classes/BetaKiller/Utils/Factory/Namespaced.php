<?php
namespace BetaKiller\Utils\Factory;

trait Namespaced {

    use Base;

    protected function make_instance_class_name($name)
    {
        $output = array();

        foreach ($this->get_namespaces() as $ns)
        {
            $output[] = $ns.'\\'.$name;
        }

        return $output;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function get_namespaces()
    {
        throw new Exception('Define namespaces in :class', array(':class' => __CLASS__));
    }

}
