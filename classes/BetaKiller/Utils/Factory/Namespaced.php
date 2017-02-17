<?php
namespace BetaKiller\Utils\Factory;

use BetaKiller\Utils\Factory\Exception;

trait Namespaced {

    use BaseFactoryTrait;

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
     * @throws \BetaKiller\Utils\Factory\Exception
     */
    protected function get_namespaces()
    {
        throw new Exception('Define namespaces in :class', array(':class' => __CLASS__));
    }

}
