<?php
namespace BetaKiller\Utils\Kohana;

abstract class TreeModelSingleParentOrm extends TreeModelOrmBase implements TreeModelSingleParentInterface
{
    use TreeModelSingleParentTrait;

    protected function _initialize()
    {
        $this->belongs_to([
            'parent' => [
                'model'       => $this->getModelName(),
                'foreign_key' => $this->getParentIdColumnName(),
            ],
        ]);

        if ($this->loadWithParent()) {
            $this->load_with(['parent']);
        }

        parent::_initialize();
    }

    protected function loadWithParent(): bool
    {
        return true;
    }

    protected function getParentIdColumnName(): string
    {
        return 'parent_id';
    }

    /**
     * Return parent iface model or NULL
     *
     * @return $this|static|null
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function getParent()
    {
        /** @var static $parent */
        $parent = $this->get('parent');

        return $parent->loaded() ? $parent : null;
    }

    /**
     * @param TreeModelSingleParentInterface|null $parent
     *
     * @return $this
     */
    public function setParent(TreeModelSingleParentInterface $parent = null)
    {
        return $this->set('parent', $parent);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\TreeModelSingleParentInterface|null $parent
     *
     * @return $this
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function filter_parent(TreeModelSingleParentInterface $parent = null)
    {
        $col = $this->object_column($this->getParentIdColumnName());

        if ($parent) {
            $this->where($col, '=', $parent->getID());
        } else {
            $this->where($col, 'IS', null);
        }

        return $this;
    }

    /**
     * @param int[]|null $parentIDs
     *
     * @return $this
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    protected function filterParentIDs($parentIDs = null)
    {
        $parentIdCol = $this->object_column($this->getParentIdColumnName());

        if ($parentIDs) {
            $this->where($parentIdCol, 'IN', (array)$parentIDs);
        } else {
            $this->where($parentIdCol, 'IS', null);
        }

        return $this;
    }
}
