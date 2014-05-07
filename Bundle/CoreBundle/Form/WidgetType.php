<?php
namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\Form\EntityProxyFormType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


/**
 * WidgetRedactor form type
 */
class WidgetType extends AbstractType
{


    protected $entity_name;
    protected $namespace;

    public function __construct($entity_name, $namespace)
    {
        $this->namespace = $namespace;
        $this->entity_name = $entity_name;

    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // $builder
        //     ->add('page', 'choice',
        //         array(
        //             "mapped" => false,
        //             "label" => null,
        //             "attr"  => array("class" => "hide")
        //         )
        //     );

        if ($this->entity_name !== null) {
            $builder
                ->add('page', null,
                    array(
                        "label" => null,
                        "attr"  => array("class" => "hide")
                    )
                )
                ->add('slot', 'hidden')
                ->add('fields', 'widget_fields', array(
                    "label" => 'widget.form.fields.label',
                    "namespace" => $this->namespace,
                    "widget"    => $options['widget']
                ))
                ->add('entity', 'entity_proxy', array(
                    "entity_name" => $this->entity_name,
                    "namespace"   => $this->namespace,
                    "widget"      => $options['widget']
                ))
                //
                ;
        }
    }


    /**
     * bind form to WidgetRedactor entity
     * @param OptionsResolverInterface $resolver
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
     */
    public function getName()
    {
        return 'appventus_victoireCoreBundle_widgettype';
    }
}
