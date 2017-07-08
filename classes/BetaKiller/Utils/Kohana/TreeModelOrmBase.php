<?php
namespace BetaKiller\Utils\Kohana;

abstract class TreeModelOrmBase extends \ORM
{
    /**
     * @param int[]|null $parentIDs
     *
     * @return $this
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    abstract protected function filterParentIDs($parentIDs = null);

    /**
     * Place here additional query params
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    abstract protected function additionalTreeTraversalFiltering();

    /**
     * @return $this[]
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function getRoot()
    {
        return $this->getChildrenByParentIDs();
    }

    /**
     * Returns list of child iface models
     *
     * @return $this[]
     * @todo Rewrite this to tree model repository
     * @deprecated
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
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    private function getChildrenByParentIDs($parent_ids = null, $key = null, $value = null)
    {
        /** @var TreeModelOrmBase $model */
        $model = $this->model_factory();

        $model->filterParentIDs($parent_ids);

        $model->additionalTreeTraversalFiltering();

        return $model
            ->cached()
            ->find_all()
            ->as_array($key, $value);
    }

    /**
     * @param null $column
     *
     * @return array
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function getAllChildren($column = null)
    {
        return $this->getAllChildrenByParentID($this->pk(), $column);
    }

    /**
     * @param null $parentID
     * @param null $column
     *
     * @return array
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
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
