<?php
namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Victoire\Bundle\PageBundle\Entity\PageStatus;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Edit Blog Type
 */
class BlogSettingsType extends BlogType
{

    public function __construct($available_locales, RequestStack $requestStack)
    {
        parent::__construct($available_locales, $requestStack);
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm( FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('slug', null, array(
                'label' => 'form.page.type.slug.label'
            ))
            ->add('status', 'choice', array(
                'label'   => 'form.page.type.status.label',
                'choices' => array(
                    PageStatus::DRAFT       => 'form.page.type.status.choice.label.draft',
                    PageStatus::PUBLISHED   => 'form.page.type.status.choice.label.published',
                    PageStatus::UNPUBLISHED => 'form.page.type.status.choice.label.unpublished',
                    PageStatus::SCHEDULED   => 'form.page.type.status.choice.label.scheduled',
                )
            ))
            ->add('publishedAt', null, array(
                'widget'         => 'single_text',
                'datetimepicker' => true
            ))
            ->add(
                'categories',
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
        return 'victoire_blog_settings_type';
    }
}
