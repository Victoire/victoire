<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AsynchronousType extends AbstractType
{
    public function getParent()
    {
        return CheckboxType::class;
    }
}