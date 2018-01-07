<?php
namespace BetaKiller\Utils\Factory;


trait NamespacedFactoryTrait
{
    use BaseFactoryTrait;

    protected function make_instance_class_name($name)
    {
        $output = [];

        foreach ($this->get_namespaces() as $ns) {
            $output[] = $ns.'\\'.$name;
        }

        return $output;
    }

    /**
     * @return array
     */
    protected function get_namespaces()
    {
        throw new Exception('Define namespaces in :class', array(':class' => __CLASS__));
    }

}
