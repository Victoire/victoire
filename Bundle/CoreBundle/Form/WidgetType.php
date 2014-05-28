<?php
namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\Form\EntityProxyFormType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Victoire\Bundle\CoreBundle\Entity\Widget;

/**
 * WidgetRedactor form type
 */
class WidgetType extends AbstractType
{
    protected $entity_name;
    protected $namespace;

    /**
     * @param string $entity_name The entity name
     * @param string $namespace   The entity namespace
     *
     * @throws Exception
     */
    public function __construct($entity_name, $namespace)
    {
        $this->namespace = $namespace;
        $this->entity_name = $entity_name;

        if ($this->entity_name !== null) {
            if ($this->namespace === null) {
                throw new \Exception('The namespace is mandatory if the entity_name is given.');
            }
        }
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder The builder
     * @param array                $options The options
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //the mode of the widget
        $mode = Widget::MODE_STATIC;

        if ($this->entity_name !== null) {

            $mode = Widget::MODE_ENTITY;

            $builder
                ->add('slot', 'hidden')
                ->add('fields', 'widget_fields', array(
                    'label' => 'widget.form.fields.label',
                    'namespace' => $this->namespace,
                    'widget'    => $options['widget']
                ))
                ->add('entity_proxy', 'entity_proxy', array(
                    'entity_name' => $this->entity_name,
                    'namespace'   => $this->namespace,
                    'widget'      => $options['widget']
                ));
        }

        //add the mode to the form
        $builder->add('mode', 'hidden', array(
            'data' => $mode
        ));
    }


    /**
     * bind form to WidgetRedactor entity
     * @param OptionsResolverInterface $resolver
     *
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Victoire\RedactorBundle\Entity\Widget',
            'widget'             => null,
            'translation_domain' => 'victoire'
        ));
    }

    /**
     * get form name
     *
     * @return string
     */
    public function getName()
    {
        return 'appventus_victoirecorebundle_widgettype';
    }
}
