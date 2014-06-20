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
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //memorize options for the pre submit
        $this->options = $options;

        $namespace = $options['namespace'];
        $entityName = $options['entityName'];
        $mode = $options['mode'];

        if ($entityName !== null) {
            if ($namespace === null) {
                throw new \Exception('The namespace is mandatory if the entity_name is given.');
            }
            if ($mode === null) {
                throw new \Exception('The mode is mandatory if the entity_name is given.');
            }
        }

        //if no mode is specified, the static is used by default
        if ($mode === null) {
            $mode = Widget::MODE_STATIC;
        }

        if ($mode === Widget::MODE_ENTITY) {
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

        if ($mode === Widget::MODE_QUERY) {
            $builder->add('query');
            $builder->add('fields', 'widget_fields', array(
                'label' => 'widget.form.fields.label',
                'namespace' => $namespace,
                'widget'    => $options['widget']
            ));
        }
        if ($mode === Widget::MODE_BUSINESS_ENTITY) {
            $builder->add('fields', 'widget_fields', array(
                'label' => 'widget.form.fields.label',
                'namespace' => $namespace,
                'widget'    => $options['widget']
            ));
        }

        //add the mode to the form
        $builder->add('mode', 'hidden', array(
            'data' => $mode
        ));

        //we use the PRE_SUBMIT event to set the mode option
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event)
            {
                $options = $this->options;

                //we get the raw data for the widget form
                $rawData = $event->getData();

                //get the posted mode
                $mode = $rawData['mode'];

                //get the form to add more fields
                $form = $event->getForm();

                //the controller does not use the mode to construct the form, so we update it automatically
                if ($mode === Widget::MODE_ENTITY) {
                    $form
                    ->add('slot', 'hidden')
                    ->add('fields', 'widget_fields', array(
                        'label' => 'widget.form.fields.label',
                        'namespace' => $options['namespace'],
                        'widget'    => $options['widget']
                    ))
                    ->add('entity_proxy', 'entity_proxy', array(
                        'entity_name' => $options['entityName'],
                        'namespace' => $options['namespace'],
                        'widget'      => $options['widget']
                    ));
                }

                if ($mode === Widget::MODE_QUERY) {
                    $form->add('query');
                    $form->add('fields', 'widget_fields', array(
                        'label' => 'widget.form.fields.label',
                        'namespace' => $options['namespace'],
                        'widget'    => $options['widget']
                    ));
                }
                if ($mode === Widget::MODE_BUSINESS_ENTITY) {
                    $form->add('fields', 'widget_fields', array(
                        'label' => 'widget.form.fields.label',
                        'namespace' => $options['namespace'],
                        'widget'    => $options['widget']
                    ));
                }
            }
        );
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

        $resolver->setOptional(array('mode'));
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
}
