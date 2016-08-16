<?php

namespace Victoire\Bundle\CriteriaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CriteriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', HiddenType::class, [
            'label' => 'victoire_criteria.criteria.name.label',
        ]);
        $builder->add('operator', TextType::class, [
            'vic_help_block'    => 'victoire_criteria.criteria.operator.help_block',
            'label'             => 'victoire_criteria.criteria.operator.label',
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                //we get the raw data for the widget form
                $name = $event->getData()->getName();
                $formParams = $options['dataSources']->getDataSource($name)->{$options['dataSources']->getDataSourceParameters($name)['method'].'FormParams'}();
                $event->getForm()->add('value', $formParams['type'], array_merge($formParams['options'], ['required' => false]));
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\CriteriaBundle\Entity\Criteria',
            'translation_domain' => 'victoire',
        ]);
        $resolver->setDefined([
            'dataSources',
        ]);
    }
}
