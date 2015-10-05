<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\PageBundle\Form\BasePageType;

/**
 * Blog form type.
 */
class BlogType extends BasePageType
{
    /*
    * Constructor
    */

    public function __construct($availableLocales, RequestStack $requestStack)
    {
        parent::__construct($availableLocales, $requestStack);
    }

    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    /**
     * bind to Page entity.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'Victoire\Bundle\BlogBundle\Entity\Blog',
                'translation_domain' => 'victoire',
            ]
        );
    }

    /**
     * get form name.
     *
     * @return string The name of the form
     */
    public function getName()
    {
        return 'victoire_blog_type';
    }
}
