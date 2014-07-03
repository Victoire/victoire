<?php

namespace Victoire\Bundle\BusinessEntityTemplateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 * @author Thomas Beaujean
 *
 */
class BusinessEntityTemplateType extends AbstractType
{
    protected $layouts;

    /**
     *
     * @param unknown $layouts
     */
    public function __construct($layouts)
    {
        $this->layouts = $layouts;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('businessEntityId', 'hidden')
            ->add('name')
            ->add('layout', 'choice', array(
                'label' => 'form.page.type.layout.label',
                'choices' => $this->layouts
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'victoire_business_entity_template_type';
    }
}
