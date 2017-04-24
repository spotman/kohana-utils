<?php

namespace BetaKiller\Utils\Kohana;

interface TreeModelMultipleParentsInterface
{
    /**
     * @return int
     */
    public function get_id(); // BC for Kohana ORM models

    /**
     * Return parents models
     *
     * @return $this[]
     */
    public function getParents();

    /**
     * Return all parent models including in hierarchy
     *
     * @return $this[]
     */
    public function getAllParents();

    /**
     * @param TreeModelMultipleParentsInterface $parent
     *
     * @return $this
     */
    public function addParent(TreeModelMultipleParentsInterface $parent);

    /**
     * @param TreeModelMultipleParentsInterface $parent
     *
     * @return $this
     */
    public function removeParent(TreeModelMultipleParentsInterface $parent);

    /**
     * @return $this[]
     */
    public function getRoot();

    /**
     * Returns list of child iface models
     *
     * @return $this[]
     */
    public function getChildren();

    /**
     * @param string|null $column
     * @return $this[]|int[]
     */
    public function getAllChildren($column = null);
}
