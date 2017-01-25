<?php
namespace BetaKiller\Utils\Kohana;

trait TreeModelTrait
{
    /**
     * @return $this[]
     */
    public function get_parents()
    {
        $parents = [];
        $current = $this;

        while ($current = $current->get_parent()) {
            $parents[] = $current;
        }

        return $parents;
    }

    /**
     * @param TreeModelSingleParentInterface $model
     *
     * @return bool
     */
    public function has_in_ascending_branch(TreeModelSingleParentInterface $model)
    {
        $current = $this;

        do {
            if ($current->get_id() == $model->get_id()) {
                return TRUE;
            }
        } while ($current = $current->get_parent());

        return FALSE;
    }
}
