<?php
namespace BetaKiller\Utils\Kohana;

abstract class TreeModelMultipleParentsOrm extends TreeModelOrmBase implements TreeModelMultipleParentsInterface
{
    abstract protected function getTreeModelThroughTableName();

    protected function _initialize()
    {
        $this->has_many([
            'parents' => [
                'model'       => $this->getModelName(),
                'foreign_key' => $this->getChildIdColumnName(),
                'far_key'     => $this->getParentIdColumnName(),
                'through'     => $this->getTreeModelThroughTableName(),
            ],
        ]);

        parent::_initialize();
    }

    protected function getChildIdColumnName(): string
    {
        return 'child_id';
    }

    protected function getParentIdColumnName(): string
    {
        return 'parent_id';
    }

    /**
     * Return parents model or null
     *
     * @return $this[]
     * @throws \Kohana_Exception
     */
    public function getParents(): array
    {
        return $this->getParentsRelation()->find_all()->as_array();
    }

    /**
     * Return all parent models including in hierarchy
     *
     * @return \BetaKiller\Utils\Kohana\TreeModelMultipleParentsInterface[]
     */
    public function getAllParents(): array
    {
        return $this->getAllParentsRecursively($this);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\TreeModelMultipleParentsInterface $child
     *
     * @return \BetaKiller\Utils\Kohana\TreeModelMultipleParentsInterface[]
     */
    protected function getAllParentsRecursively(TreeModelMultipleParentsInterface $child): array
    {
        $parents = [];

        foreach ($child->getParents() as $parent) {
            $parents[] = $parent;

            foreach ($this->getAllParentsRecursively($parent) as $grandParent) {
                $parents[] = $grandParent;
            }
        }

        return $parents;
    }

    /**
     * @return $this
     */
    protected function getParentsRelation()
    {
        return $this->get('parents');
    }

    /**
     * @param array|null $parentIDs
     *
     * @return $this
     * @throws \HTTP_Exception_501
     * @throws \Kohana_Exception
     */
    protected function filterParentIDs($parentIDs = null)
    {
        $parentsTableNameAlias = $this->table_name().'_parents';

        $this->join_related('parents', $parentsTableNameAlias);

        $parentIdCol = $parentsTableNameAlias.'.'.$this->getParentIdColumnName();

        if ($parentIDs) {
            $this->where($parentIdCol, 'IN', (array)$parentIDs);
        } else {
            $this->where($parentIdCol, 'IS', null);
        }

        return $this;
    }


    /**
     * @param TreeModelMultipleParentsInterface $parent
     *
     * @return $this
     */
    public function addParent(TreeModelMultipleParentsInterface $parent)
    {
        $this->add('parents', $parent);

        return $this;
    }

    /**
     * @param TreeModelMultipleParentsInterface $parent
     *
     * @return $this
     */
    public function removeParent(TreeModelMultipleParentsInterface $parent)
    {
        $this->remove('parents', $parent);

        return $this;
    }
}
