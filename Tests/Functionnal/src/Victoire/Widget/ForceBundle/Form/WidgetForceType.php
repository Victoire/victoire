<?php

namespace Victoire\Widget\ForceBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\Form\WidgetType;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * WidgetForce form type.
 */
class WidgetForceType extends WidgetType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     *
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['mode'] === Widget::MODE_STATIC) {
            $builder->add('side', null, [
                'label' => 'widget_force.form.side.label',
            ]);
        }
        parent::buildForm($builder, $options);
    }

    /**
     * bind form to WidgetForce entity.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults([
            'data_class'         => 'Victoire\Widget\ForceBundle\Entity\WidgetForce',
            'widget'             => 'Force',
            'translation_domain' => 'victoire',
        ]);
    }

    /**
     * get form name.
     *
     * @return string The form name
     */
    public function getName()
    {
        return 'victoire_widget_form_force';
    }
}
