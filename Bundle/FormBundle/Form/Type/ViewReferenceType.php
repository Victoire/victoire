<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ViewReferenceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'                          => 'form.link_type.view_reference.label',
            'required'                       => true,
            'attr'                           => [
                'novalidate'                   => 'novalidate',
                'tabindex'                     => '-1',
                // classes are removed in choice_widget_collapsed so we use another attribute
                'data-is-view-reference-field' => '1',
            ],
            'placeholder'                    => 'form.link_type.view_reference.blank',
            'choices_as_values'              => true,
            'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group'],
            'locale'                         => null,
        ]);
        parent::configureOptions($resolver);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
