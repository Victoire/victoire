<?php

namespace Victoire\Bundle\BlogBundle\Repository;

use Victoire\Bundle\PageBundle\Repository\BasePageRepository;

/**
 * The Page repository.
 */
class BlogRepository extends BasePageRepository
{
    public function needChooseForm()
    {
        $qb = $this->getInstance('blog')
            ->select('b_translation.id')
            ->join('blog.translations', 'b_translation');
        return count($qb->getQuery()->getResult()) > 1 ;
    }
}
