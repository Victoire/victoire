<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Base Widget form type.
 */
class CriteriaCollectionType extends CollectionType
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'criteria_collection';
    }
}
