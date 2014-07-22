<?php
namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\Form\Builder\EntityProxyFieldsBuilder;

/**
 *
 * @author Paul Andrieux
 *
 */
class WidgetFieldsFormType extends AbstractType
{
    private $entityProxyFieldsBuilder;

    /**
     * constructor
     *
     * @return void
     **/
    public function __construct (EntityProxyFieldsBuilder $entityProxyFieldsBuilder)
    {
        $this->entityProxyFieldsBuilder = $entityProxyFieldsBuilder;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entityProxyFieldsBuilder->buildForEntityAndWidgetType($builder, $options['widget'], $options['namespace']);
    }

    /**
     * bind to Menu entity
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => null,
                'namespace'          => null,
                'fields'             => array(),
                'widget'             => null,
                'translation_domain' => 'victoire'
            )
        );
    }

    /**
     * get form name
     *
     * @return string The form name
     */
    public function getName()
    {
        return 'widget_fields';
    }
}
