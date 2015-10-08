<?php

namespace Victoire\Bundle\BlogBundle\Builder;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;

class ArticleTemplateReferenceBuilder extends BaseReferenceBuilder
{
    public function buildReference(View $view, EntityManager $em)
    {
        return [];
    }
}
