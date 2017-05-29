<?php
namespace BetaKiller\Utils\Kohana;

use BetaKiller\Model\RoleInterface;

abstract class TreeModelMultipleParentsOrm extends TreeModelOrmBase implements TreeModelMultipleParentsInterface
{
    abstract protected function get_through_table_name();

    protected function _initialize()
    {
        $this->has_many([
            'parents' => [
                'model'         =>  $this->getModelName(),
                'foreign_key'   =>  $this->get_child_id_column_name(),
                'far_key'       =>  $this->get_parent_id_column_name(),
                'through'       =>  $this->get_through_table_name(),
            ],
        ]);

        parent::_initialize();
    }

    protected function get_child_id_column_name()
    {
        return 'child_id';
    }

    protected function get_parent_id_column_name()
    {
        return 'parent_id';
    }

    /**
     * Return parents model or null
     *
     * @return $this[]
     */
    public function getParents()
    {
        return $this->get_parents_relation()->find_all()->as_array();
    }

    /**
     * Return all parent models including in hierarchy
     *
     * @return $this[]
     */
    public function getAllParents()
    {
        return $this->get_role_parents_recursively($this);
    }

    protected function get_role_parents_recursively(RoleInterface $role)
    {
        $parents = [];

        $parents[$role->get_name()] = $role;

        foreach ($role->getParents() as $parent) {
            $parent_parents = $this->get_role_parents_recursively($parent);
            $parents = array_merge($parents, $parent_parents);
        }

        return $parents;
    }

    /**
     * @return $this
     */
    protected function get_parents_relation()
    {
        return $this->get('parents');
    }

    /**
     * @param int[]|null $parent_ids
     *
     * @return $this
     */
    protected function filterParentIDs($parent_ids = NULL)
    {
        $parents_table_name_alias = $this->table_name().'_parents';

        $this->join_related('parents', $parents_table_name_alias);

        $parent_id_col = $parents_table_name_alias.'.'.$this->get_parent_id_column_name();

        if ($parent_ids) {
            $this->where($parent_id_col, 'IN', (array) $parent_ids);
        } else {
            $this->where($parent_id_col, 'IS', null);
        }

        return $this;
    }



    /**
     * @param TreeModelMultipleParentsInterface $parent
     *
     * @return $this
     */
    public function addParent(TreeModelMultipleParentsInterface $parent)
    {
        $this->add('parents', $parent);
        return $this;
    }

    /**
     * @param TreeModelMultipleParentsInterface $parent
     *
     * @return $this
     */
    public function removeParent(TreeModelMultipleParentsInterface $parent)
    {
        $this->remove('parents', $parent);
        return $this;
    }
}
