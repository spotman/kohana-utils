<?php

namespace BetaKiller\Utils\Kohana;

interface TreeModelSingleParentInterface
{
    /**
     * @return int
     */
    public function get_id();

    /**
     * Return parent model or null
     *
     * @return $this|null
     */
    public function get_parent();

    /**
     * Returns list of child models
     *
     * @return $this[]
     */
    public function get_children();

    /**
     * @return $this[]
     */
    public function get_root();

    /**
     * @param string|null $column
     * @return int[]
     */
    public function get_all_children($column = null);

    /**
     * @param TreeModelSingleParentInterface|null $parent
     *
     * @return $this
     */
    public function set_parent(TreeModelSingleParentInterface $parent = null);

    /**
     * @return $this[]
     */
    public function get_parents();

    /**
     * @param TreeModelSingleParentInterface $model
     *
     * @return bool
     */
    public function has_in_ascending_branch(TreeModelSingleParentInterface $model);
}
