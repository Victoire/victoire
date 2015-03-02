<?php
namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Edit Blog categories Type
 */
class BlogCategoryType extends AbstractType
{

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm( FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'rootCategories',
                'collection',
                array(
                    'type'          => 'victoire_form_blog_category',
                    'required'      => false,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                    'prototype'     => true,                )
            );
    }

    /**
     * bind to Page entity
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(
            array(
                'cascade_validation' => 'true'
            )
        );
    }

    /**
     * get form name
     */
    public function getName()
    {
        return 'victoire_blog_category_type';
    }
}
