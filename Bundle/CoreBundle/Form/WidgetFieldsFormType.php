<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\Form\Builder\EntityProxyFieldsBuilder;

/**
 * @author Paul Andrieux
 */
class WidgetFieldsFormType extends AbstractType
{
    private $entityProxyFieldsBuilder;

    /**
     * constructor.
     *
     * @return void
     **/
    public function __construct(EntityProxyFieldsBuilder $entityProxyFieldsBuilder)
    {
        $this->entityProxyFieldsBuilder = $entityProxyFieldsBuilder;
    }

    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entityProxyFieldsBuilder->buildForEntityAndWidgetType($builder, $options['widget'], $options['namespace']);
    }

    /**
     * bind to Menu entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'namespace'          => null,
                'fields'             => [],
                'widget'             => null,
                'translation_domain' => 'victoire',
            ]
        );
    }
}
