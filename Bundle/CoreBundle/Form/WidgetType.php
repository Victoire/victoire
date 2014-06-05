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
    /**
     * define form fields
     * @param FormBuilderInterface $builder The builder
     * @param array                $options The options
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $namespace = $options['namespace'];
        $entityName = $options['entityName'];

        if ($entityName !== null) {
            if ($namespace === null) {
                throw new \Exception('The namespace is mandatory if the entity_name is given.');
            }
        }

        //the mode of the widget
        $mode = Widget::MODE_STATIC;

        if ($entityName !== null) {

            $mode = Widget::MODE_ENTITY;

            $builder
                ->add('slot', 'hidden')
                ->add('fields', 'widget_fields', array(
                    'label' => 'widget.form.fields.label',
                    'namespace' => $namespace,
                    'widget'    => $options['widget']
                ))
                ->add('entity_proxy', 'entity_proxy', array(
                    'entity_name' => $entityName,
                    'namespace'   => $namespace,
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

        $resolver->setOptional(array('namespace'));
        $resolver->setOptional(array('entityName'));
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

//     /**
//      * Get the entity name
//      *
//      * @return string
//      */
//     public function getEntityName()
//     {
//         return $entityName;
//     }
}
