<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Create an entity proxy for the widget.
 *
 * @author Paul Andrieux
 */
class EntityProxyFormType extends AbstractType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //add the link to the business entity instance
        //it depends of the form
        $builder
            ->add($options['business_entity_id'], EntityType::class, [
                'label'       => false,
                'required'    => false,
                'placeholder' => 'entity_proxy.form.empty_value',
                'class'       => $options['namespace'],
                'attr'        => [
                    'class' => 'add_'.$options['business_entity_id'].'_link picker_entity_select',
                ],
            ]);
    }

    /**
     * bind to Menu entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\CoreBundle\Entity\EntityProxy',
            'business_entity_id' => null,
            'namespace'          => null,
            'widget'             => null,
            'translation_domain' => 'victoire',
        ]);
    }
}
