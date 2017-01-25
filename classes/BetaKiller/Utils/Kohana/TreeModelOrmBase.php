<?php
namespace BetaKiller\Utils\Kohana;

abstract class TreeModelOrmBase extends \ORM
{
    use TreeModelTrait;

    /**
     * @param int[]|null $parent_ids
     *
     * @return $this
     */
    abstract protected function filter_parent_ids($parent_ids = null);

    /**
     * Place here additional query params
     */
    abstract protected function additional_tree_model_filtering();

    /**
     * @return $this[]
     */
    public function get_root()
    {
        return $this->get_children_by_parent_ids();
    }

    /**
     * Returns list of child iface models
     *
     * @return $this[]
     */
    public function get_children()
    {
        return $this->get_children_by_parent_ids($this->pk());
    }

    /**
     * @param int|int[] $parent_ids
     * @param string|null $key
     * @param string|null $value
     * @return $this[]
     */
    private function get_children_by_parent_ids($parent_ids = null, $key = null, $value = null)
    {
        /** @var TreeModelOrmBase $model */
        $model = $this->model_factory();

        $model->filter_parent_ids($parent_ids);

        $model->additional_tree_model_filtering();

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
}
