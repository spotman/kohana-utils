<?php
namespace BetaKiller\Utils\Kohana;

use BetaKiller\Model\AbstractEntityInterface;

interface TreeModelSingleParentInterface extends AbstractEntityInterface
{
    /**
     * Return parent model or null
     *
     * @return static|$this|null
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function getParent();

    /**
     * Returns list of child models
     *
     * @return $this[]|mixed
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function getChildren();

    /**
     * @return $this[]
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function getRoot();

    /**
     * @param string|null $columnName
     *
     * @return int[]
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function getAllChildren(string $columnName = null);

    /**
     * @param TreeModelSingleParentInterface|null $parent
     *
     * @return $this
     * @deprecated
     */
    public function setParent(TreeModelSingleParentInterface $parent = null);

    /**
     * @return $this[]
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function getParents();

    /**
     * @param TreeModelSingleParentInterface $model
     *
     * @return bool
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function hasInAscendingBranch(TreeModelSingleParentInterface $model);
}
