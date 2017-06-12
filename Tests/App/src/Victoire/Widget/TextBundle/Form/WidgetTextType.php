<?php

namespace Victoire\Widget\TextBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\Form\WidgetType;
use Victoire\Bundle\WidgetBundle\Model\Widget;

/**
 * WidgetText form type.
 */
class WidgetTextType extends WidgetType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($options['mode'] == Widget::MODE_STATIC || $options['mode'] === null) {
            $builder->add('content', null, [
                    'label'    => 'widget_text.form.content.label',
                    'required' => true,
                ]
            );
        } else {
            $builder->add('excerpt', null, [
                    'label'    => 'widget_text.form.excerpt.label',
                    'attr'    => [
                        'placeholder' => 'widget_text.form.excerpt.placeholder',
                    ],
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class'         => 'Victoire\Widget\TextBundle\Entity\WidgetText',
            'translation_domain' => 'victoire',
            'widget'             => 'Text',
        ]);
    }
}
