<?php
/**
 * Created by PhpStorm.
 * User: paulandrieux
 * Date: 21/03/2016
 * Time: 09:09
 */

namespace Victoire\Bundle\CriteriaBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CriteriaBundle\Chain\DataSourceChain;

class CriteriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name', HiddenType::class);
        $builder->add('operator', ChoiceType::class, [
            'choices' => ['equal' => 'equal']
        ]);


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                //we get the raw data for the widget form
                $name = $event->getData()->getName();
                $formParams = $options['dataSources']->getDataSource($name)->{$options['dataSources']->getDataSourceParameters($name)['method']."FormParams"}();
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
