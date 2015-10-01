<?php

namespace Victoire\Bundle\PageBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Page Type.
 */
class PageType extends BasePageType
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
        $builder
            ->add('homepage', 'hidden');
    }

    /**
     * bind to Page entity.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\PageBundle\Entity\Page',
            'translation_domain' => 'victoire',
        ]);
    }

    /**
     * get form name.
     *
     * @return string name
     */
    public function getName()
    {
        return 'victoire_page_type';
    }
}
