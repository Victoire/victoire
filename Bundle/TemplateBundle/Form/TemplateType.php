<?php

namespace Victoire\Bundle\TemplateBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\Form\ViewType;

/**
 * Template type
 */
class TemplateType extends ViewType
{

    protected $layouts;

    /**
     * constructor
     * @param EntityManager $layouts
     */
    public function __construct($layouts)
    {
        $this->layouts = $layouts;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('layout', 'choice', array(
                'label' => 'form.template.type.layout.label',
                'choices' => $options['layouts']
            )
        );
    }

    /**
     * bind to Template entity
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Victoire\Bundle\TemplateBundle\Entity\Template',
                'translation_domain' => 'victoire',
                'layouts'            => $this->layouts
            )
        );
    }

    /**
     * get form name
     */
    public function getName()
    {
        return 'victoire_template_type';
    }
}
