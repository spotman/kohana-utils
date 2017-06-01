<?php

namespace BetaKiller\Utils\Kohana;

interface TreeModelSingleParentInterface
{
    /**
     * Redundant fix for autocomplete ORM::get_id();
     * @return int
     */
    public function get_id();

    /**
     * Return parent model or null
     *
     * @return $this|null
     */
    public function getParent();

    /**
     * Returns list of child models
     *
     * @return $this[]|mixed
     */
    public function getChildren();

    /**
     * @return $this[]
     */
    public function getRoot();

    /**
     * @param string|null $column
     * @return int[]
     */
    public function getAllChildren($column = null);

    /**
     * @param TreeModelSingleParentInterface|null $parent
     *
     * @return $this
     */
    public function setParent(TreeModelSingleParentInterface $parent = null);

    /**
     * @return $this[]
     */
    public function getParents();

    /**
     * @param TreeModelSingleParentInterface $model
     *
     * @return bool
     */
    public function hasInAscendingBranch(TreeModelSingleParentInterface $model);
}
