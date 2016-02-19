<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Edit Blog categories Type.
 */
class BlogCategoryType extends AbstractType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'rootCategories',
                'collection',
                [
                    'type'          => self::class,
                    'required'      => false,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                    'prototype'     => true, ]
            );
    }

    /**
     * bind to Page entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'cascade_validation' => 'true',
            ]
        );
    }
}
