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
                'foreign_key' => $this->get_parent_id_column_name(),
            ],
        ]);

        if ($this->load_with_parent()) {
            $this->load_with(['parent']);
        }

        parent::_initialize();
    }

    protected function load_with_parent()
    {
        return true;
    }

    protected function get_parent_id_column_name()
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

        return $parent->loaded() ? $parent : NULL;
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
        $col = $this->object_column($this->get_parent_id_column_name());

        if ($parent) {
            $this->where($col, '=', $parent->get_id());
        } else {
            $this->where($col, 'IS', null);
        }

        return $this;
    }

    /**
     * @param int[]|null $parent_ids
     *
     * @return $this
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    protected function filterParentIDs($parent_ids = NULL)
    {
        $parent_id_col = $this->object_column($this->get_parent_id_column_name());

        if ($parent_ids) {
            $this->where($parent_id_col, 'IN', (array) $parent_ids);
        } else {
            $this->where($parent_id_col, 'IS', null);
        }

        return $this;
    }
}
