<?php

namespace Victoire\Bundle\BusinessEntityPageBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\Form\ViewType;

/**
 * businessEntitypagePatternType
 */
class BusinessEntityPagePatternType extends ViewType
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
            ->add('businessEntityName', 'hidden')
            ->add('query')
            ->add('slug', null, array(
                    'label' => 'victoire.form.business_entity_page_pattern.slug.label'
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setOptional(array('businessProperty'));

        $resolver->setDefaults(array(
            'data_class' => 'Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern'
        ));
    }

    /**
     * The name of the form
     *
     * @return string
     */
    public function getName()
    {
        return 'victoire_business_entity_page_type';
    }
}
