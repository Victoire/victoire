<?php

namespace Victoire\Widget\ForceBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
     * @param FormBuilderInterface $builder
     * @param array                $options
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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => 'Victoire\Widget\ForceBundle\Entity\WidgetForce',
            'widget' => 'Force',
            'translation_domain' => 'victoire',
        ]);
    }
}
