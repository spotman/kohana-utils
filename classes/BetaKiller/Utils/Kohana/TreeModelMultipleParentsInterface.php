<?php

namespace BetaKiller\Utils\Kohana;

interface TreeModelMultipleParentsInterface
{
    /**
     * @return int
     */
    public function get_id();

    /**
     * Return parents models
     *
     * @return $this[]
     */
    public function get_parents();

    /**
     * @param TreeModelMultipleParentsInterface $parent
     *
     * @return $this
     */
    public function add_parent(TreeModelMultipleParentsInterface $parent);

    /**
     * @param TreeModelMultipleParentsInterface $parent
     *
     * @return $this
     */
    public function remove_parent(TreeModelMultipleParentsInterface $parent);

    /**
     * @return $this[]
     */
    public function get_root();

    /**
     * Returns list of child iface models
     *
     * @return $this[]
     */
    public function get_children();

    /**
     * @param string|null $column
     * @return $this[]|int[]
     */
    public function get_all_children($column = null);
}
