<?php
namespace BetaKiller\Utils\Kohana;

abstract class TreeModelMultipleParentsOrm extends TreeModelOrmBase implements TreeModelMultipleParentsInterface
{
    abstract protected function get_through_table_name();

    protected function _initialize()
    {
        $this->has_many([
            'parents' => [
                'model'         =>  $this->get_model_name(),
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
    public function get_parents()
    {
        return $this->get_parents_relation()->find_all()->as_array();
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
    protected function filter_parent_ids($parent_ids = NULL)
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
    public function add_parent(TreeModelMultipleParentsInterface $parent)
    {
        $this->add('parents', $parent);
        return $this;
    }

    /**
     * @param TreeModelMultipleParentsInterface $parent
     *
     * @return $this
     */
    public function remove_parent(TreeModelMultipleParentsInterface $parent)
    {
        $this->remove('parents', $parent);
        return $this;
    }
}
