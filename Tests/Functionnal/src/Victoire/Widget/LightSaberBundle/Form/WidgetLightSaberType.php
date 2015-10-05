<?php

namespace Victoire\Widget\LightSaberBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\Form\WidgetType;

/**
 * WidgetLightSaber form type.
 */
class WidgetLightSaberType extends WidgetType
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
     * bind form to WidgetLightSaber entity.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults([
            'data_class'         => 'Victoire\Widget\LightSaberBundle\Entity\WidgetLightSaber',
            'widget'             => 'LightSaber',
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
        return 'victoire_widget_form_lightsaber';
    }
}
