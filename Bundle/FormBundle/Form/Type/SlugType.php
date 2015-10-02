<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Victoire\Bundle\FormBundle\Form\DataTransformer\StringToSlugTransformer;

class SlugType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new StringToSlugTransformer();
        $builder->addModelTransformer($transformer);
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'slug';
    }
}
