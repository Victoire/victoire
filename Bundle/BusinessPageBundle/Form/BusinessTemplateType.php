<?php

namespace Victoire\Bundle\BusinessPageBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\Form\ViewType;

/**
 * BusinessTemplateType.
 */
class BusinessTemplateType extends ViewType
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

        $builder
            ->add('authorRestricted')
            ->add('businessEntityId', 'hidden')
            ->add('query', null, [
                    'label' => 'victoire.form.business_template.query.label',

                ])->add('slug', null, [
                    'label' => 'victoire.form.business_template.slug.label',
                ]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setOptional(['businessProperty']);

        $resolver->setDefaults([
                'data_class'        => 'Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate',
                'translationDomain' => 'victoire',
        ]);
    }

    /**
     * The name of the form.
     *
     * @return string
     */
    public function getName()
    {
        return 'victoire_business_template_type';
    }
}
