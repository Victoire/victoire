<?php

namespace Victoire\Widget\AnakinBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\Form\WidgetType;

/**
 * WidgetAnakin form type
 */
class WidgetAnakinType extends WidgetType
{
    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
                $builder->add('side', null, array(
            'label' => 'widget_anakin.form.side.label'
        ));
                parent::buildForm($builder, $options);

    }

    /**
     * bind form to WidgetAnakin entity
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'data_class'         => 'Victoire\Widget\AnakinBundle\Entity\WidgetAnakin',
            'widget'             => 'Anakin',
            'translation_domain' => 'victoire'
        ));
    }

    /**
     * get form name
     *
     * @return string The form name
     */
    public function getName()
    {
        return 'victoire_widget_form_anakin';
    }
}
