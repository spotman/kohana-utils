<?php
namespace BetaKiller\Utils\Kohana;

interface TreeModelMultipleParentsInterface
{
    /**
     * Return parents models
     *
     * @return \BetaKiller\Utils\Kohana\TreeModelMultipleParentsInterface[]|mixed
     */
    public function getParents(): array;

    /**
     * Return all parent models including in hierarchy
     *
     * @return \BetaKiller\Utils\Kohana\TreeModelMultipleParentsInterface[]|mixed
     */
    public function getAllParents(): array;

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
     * @param string|null $columnName
     *
     * @return \BetaKiller\Utils\Kohana\TreeModelMultipleParentsInterface[]|int[]
     */
    public function getAllChildren(string $columnName = null);
}
