<?php

namespace Victoire\Bundle\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessPropertyPickerTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['vic_business_properties'] = $options['vic_business_properties'];
        $view->vars['vic_business_property_picker'] = $options['vic_business_property_picker'];

        if (is_array($options['vic_business_property_picker'])) {
            if (array_key_exists('description', $options['vic_business_property_picker'])) {
                $view->vars['vic_business_property_picker_description'] = $options['vic_business_property_picker']['description'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'vic_business_property_picker'             => null,
            'vic_business_property_picker_description' => null,
            'vic_business_properties'                  => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
