<?php

namespace BetaKiller\Utils\Kohana;

interface TreeModelInterface
{
    /**
     * @return int
     */
    public function get_id();

    /**
     * Return parent iface model or null
     *
     * @return $this|null
     */
    public function get_parent();

    /**
     * Returns list of child iface models
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
     * @param TreeModelInterface|null $parent
     * @return $this
     */
    public function set_parent(TreeModelInterface $parent = null);
}
