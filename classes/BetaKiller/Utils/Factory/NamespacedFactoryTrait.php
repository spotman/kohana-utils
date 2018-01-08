<?php
namespace BetaKiller\Utils\Factory;


/**
 * Trait NamespacedFactoryTrait
 *
 * @package BetaKiller\Utils\Factory
 * @deprecated use NamespaceBasedFactory instead
 */
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
        throw new FactoryException('Define namespaces in :class', array(':class' => __CLASS__));
    }

}
