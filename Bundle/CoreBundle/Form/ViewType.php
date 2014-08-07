<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Page Type
 */
abstract class ViewType extends AbstractType
{

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'label' => 'form.view.type.name.label'
            ))
            ->add('parent', null, array(
                'label' => 'form.view.type.parent.label'
            ))
            ->add('template', null, array(
                'label' => 'form.view.type.template.label'
            ));
    }

}
