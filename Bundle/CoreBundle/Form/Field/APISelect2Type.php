<?php

namespace Victoire\Bundle\CoreBundle\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class APISelect2Type extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }
}
