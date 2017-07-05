<?php
namespace BetaKiller\Utils\Kohana;

trait TreeModelSingleParentTrait
{
    /**
     * @return $this[]
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function getParents()
    {
        /** @var \BetaKiller\Utils\Kohana\TreeModelSingleParentInterface $current */
        $current = $this;
        $parents = [];

        while ($current = $current->getParent()) {
            $parents[] = $current;
        }

        return $parents;
    }

    /**
     * @param TreeModelSingleParentInterface $model
     *
     * @return bool
     * @todo Rewrite this to tree model repository
     * @deprecated
     */
    public function hasInAscendingBranch(TreeModelSingleParentInterface $model)
    {
        /** @var \BetaKiller\Utils\Kohana\TreeModelSingleParentInterface $current */
        $current = $this;

        do {
            if ($current->get_id() === $model->get_id()) {
                return TRUE;
            }
        } while ($current = $current->getParent());

        return FALSE;
    }
}
