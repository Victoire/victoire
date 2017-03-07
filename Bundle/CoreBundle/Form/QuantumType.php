<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class QuantumType extends AbstractType
{
    public function getParent()
    {
        return TextType::class;
    }
}
