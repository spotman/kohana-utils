<?php
namespace BetaKiller\Utils\Kohana;

abstract class TreeModel extends \ORM implements TreeModelInterface
{
    protected function _initialize()
    {
        $this->belongs_to(array(
            'parent' => array(
                'model'       => $this->get_model_name(),
                'foreign_key' => $this->get_parent_id_column_name(),
            ),
        ));

        $this->load_with(array(
            'parent',
        ));

        parent::_initialize();
    }

    protected function get_parent_id_column_name()
    {
        return 'parent_id';
    }

    /**
     * Returns list of child iface models
     *
     * @return \BetaKiller\Utils\Kohana\TreeModel[]
     */
    public function get_children()
    {
        return $this->get_children_by_parent_ids($this->pk());
    }

    /**
     * @param int|int[] $parent_ids
     * @param string|null $key
     * @param string|null $value
     * @return \BetaKiller\Utils\Kohana\TreeModel[]
     */
    private function get_children_by_parent_ids($parent_ids, $key = null, $value = null)
    {
        $parent_id_col = $this->object_column($this->get_parent_id_column_name());

        $model = $this->model_factory();

        if ($parent_ids) {
            $model->where($parent_id_col, 'IN', (array) $parent_ids);
        } else {
            $model->where($parent_id_col, 'IS', null);
        }

        return $model
            ->cached()
            ->find_all()
            ->as_array($key, $value);
    }

    public function get_all_children($column = null)
    {
        return $this->get_all_children_by_parent_id($this->pk(), $column);
    }

    protected function get_all_children_by_parent_id($parent_id = null, $column = null)
    {
        $ids = [];
        $parent_ids = (array) $parent_id;

        do {
            $layer_ids = $this->get_children_by_parent_ids($parent_ids, null, $column);

            $ids = array_merge($ids, $layer_ids);
            $parent_ids = $layer_ids;
        } while ($parent_ids);

        return $ids;
    }

    /**
     * Return parent iface model or NULL
     *
     * @return $this
     */
    public function get_parent()
    {
        /** @var static $parent */
        $parent = $this->get('parent');

        return $parent->loaded() ? $parent : NULL;
    }

    /**
     * @param TreeModelInterface|null $parent
     * @return $this
     */
    public function set_parent(TreeModelInterface $parent = null)
    {
        return $this->set('parent', $parent);
    }

    public function filter_parent(TreeModelInterface $parent = null)
    {
        $col = $this->object_column($this->get_parent_id_column_name());

        if ($parent) {
            $this->where($col, '=', $parent->get_id());
        } else {
            $this->where($col, 'IS', null);
        }

        return $this;
    }
}