<?php

namespace Victoire\Bundle\BusinessPageBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\PageBundle\Form\PageSettingsType;

/**
 * BusinessPageType.
 */
class BusinessPageType extends PageSettingsType
{
    /*
    * Constructor
    */

    public function __construct($availableLocales, RequestStack $requestStack)
    {
        parent::__construct($availableLocales, $requestStack);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('slug');
        $builder->add('slug', 'hidden');
        $builder->add('staticUrl', 'slug', [
                'label' => 'form.page.type.slug.label',
            ]
        );
    }
}
