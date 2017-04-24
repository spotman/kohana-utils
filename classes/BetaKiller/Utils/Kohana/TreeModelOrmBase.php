<?php
namespace BetaKiller\Utils\Kohana;

abstract class TreeModelOrmBase extends \ORM
{
    /**
     * @param int[]|null $parentIDs
     *
     * @return $this
     */
    abstract protected function filterParentIDs($parentIDs = null);

    /**
     * Place here additional query params
     */
    abstract protected function additionalTreeModelFiltering();

    /**
     * @return $this[]
     */
    public function getRoot()
    {
        return $this->getChildrenByParentIDs();
    }

    /**
     * Returns list of child iface models
     *
     * @return $this[]
     */
    public function getChildren()
    {
        return $this->getChildrenByParentIDs($this->pk());
    }

    /**
     * @param int|int[] $parent_ids
     * @param string|null $key
     * @param string|null $value
     * @return $this[]
     */
    private function getChildrenByParentIDs($parent_ids = null, $key = null, $value = null)
    {
        /** @var TreeModelOrmBase $model */
        $model = $this->model_factory();

        $model->filterParentIDs($parent_ids);

        $model->additionalTreeModelFiltering();

        return $model
            ->cached()
            ->find_all()
            ->as_array($key, $value);
    }

    public function getAllChildren($column = null)
    {
        return $this->getAllChildrenByParentID($this->pk(), $column);
    }

    protected function getAllChildrenByParentID($parentID = null, $column = null)
    {
        $ids = [];
        $parentIDs = (array) $parentID;

        do {
            $layer_ids = $this->getChildrenByParentIDs($parentIDs, null, $column);

            $ids = array_merge($ids, $layer_ids);
            $parentIDs = $layer_ids;
        } while ($parentIDs);

        return $ids;
    }
}
