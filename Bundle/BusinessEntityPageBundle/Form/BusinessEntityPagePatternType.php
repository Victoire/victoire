<?php

namespace Victoire\Bundle\BusinessEntityPageBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\PageBundle\Form\PageType;

/**
 *
 */
class BusinessEntityPagePatternType extends PageType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $businessProperty = $options['businessProperty'];

        $builder
            ->add('businessEntityName', 'hidden')
            ->add('name')
            ->add('query')
            ->add('url');

        parent::buildForm($builder, $options);
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
