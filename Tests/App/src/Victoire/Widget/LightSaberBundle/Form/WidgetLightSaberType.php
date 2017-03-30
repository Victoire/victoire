<?php

namespace Victoire\Widget\LightSaberBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\Form\WidgetType;

/**
 * WidgetLightSaber form type.
 */
class WidgetLightSaberType extends WidgetType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('length', null, [
                    'label' => 'widget_lightsaber.form.length.label',
            ])->add('crystal', null, [
                    'label' => 'widget_lightsaber.form.crystal.label',
            ])->add('color', null, [
                    'label' => 'widget_lightsaber.form.color.label',
            ]);
        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class'         => 'Victoire\Widget\LightSaberBundle\Entity\WidgetLightSaber',
            'widget'             => 'LightSaber',
            'translation_domain' => 'victoire',
        ]);
    }
}
