<?php

namespace Victoire\Bundle\BusinessPageBundle\Builder;

use Doctrine\ORM\EntityManager;
use Metadata\ClassMetadata;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * VirtualBusinessPageReferenceBuilder.
 */
class VirtualBusinessPageReferenceBuilder extends BusinessPageReferenceBuilder
{
    public function buildReference(View $businessPage, EntityManager $em)
    {
        $businessPageReference = null;
        $businessEntityMetadata = new ClassMetadata(get_class($businessPage->getEntityProxy()->getEntity()));
        if ($businessEntityMetadata->name !== 'Victoire\Bundle\BlogBundle\Entity\Article') {
            $businessPageReference = parent::buildReference($businessPage, $em);
        }

        return $businessPageReference;
    }
}
