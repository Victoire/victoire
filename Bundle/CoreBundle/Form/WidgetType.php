<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\WidgetBundle\Model\Widget;

/**
 * Base Widget form type.
 */
class WidgetType extends AbstractType
{
    /**
     * Define form fields.
     *
     * @param FormBuilderInterface $builder The builder
     * @param array                $options The options
     *
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['businessEntityId'] !== null) {
            if ($options['namespace'] === null) {
                throw new \Exception('The namespace is mandatory if the business_entity_id is given.');
            }
            if ($options['mode'] === null) {
                throw new \Exception('The mode is mandatory if the business_entity_id is given.');
            }
        }

        if ($options['mode'] === Widget::MODE_ENTITY) {
            $this->addEntityFields($builder, $options);
        }

        if ($options['mode'] === Widget::MODE_QUERY) {
            $this->addQueryFields($builder, $options);
        }

        if ($options['mode'] === Widget::MODE_BUSINESS_ENTITY) {
            $this->addBusinessEntityFields($builder, $options);
        }

        //add the mode to the form
        $builder->add('mode', HiddenType::class, [
            'data' => $options['mode'],
        ]);
        $builder->add('asynchronous', null, [
                'label'    => 'victoire.widget.type.asynchronous.label',
                'required' => false,
            ]);
        $builder->add('theme', HiddenType::class);

        //add the slot to the form
        $builder->add('slot', HiddenType::class, []);

        //we use the PRE_SUBMIT event to set the mode option
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($options) {
                //we get the raw data for the widget form
                $rawData = $event->getData();

                //get the posted mode
                $mode = $rawData['mode'];

                //get the form to add more fields
                $form = $event->getForm();

                //the controller does not use the mode to construct the form, so we update it automatically
                if ($mode === Widget::MODE_ENTITY) {
                    $this->addEntityFields($form, $options);
                }

                if ($mode === Widget::MODE_QUERY) {
                    $this->addQueryFields($form, $options);
                }
                if ($mode === Widget::MODE_BUSINESS_ENTITY) {
                    $this->addBusinessEntityFields($form, $options);
                }
            }
        );
    }

    /**
     * Add the fields for the business entity mode.
     *
     * @param FormBuilderInterface|FormInterface $form
     * @param array                              $options
     */
    protected function addBusinessEntityFields($form, $options)
    {
        $form->add('fields', WidgetFieldsFormType::class, [
            'label'     => 'widget.form.entity.fields.label',
            'namespace' => $options['namespace'],
            'widget'    => $options['widget'],
        ]);
    }

    /**
     * Add the fields for the form and the entity mode.
     *
     * @param FormBuilderInterface|FormInterface $form
     * @param array                              $options
     */
    protected function addEntityFields($form, $options)
    {
        $form
        ->add('fields', WidgetFieldsFormType::class, [
            'label'     => 'widget.form.entity.fields.label',
            'namespace' => $options['namespace'],
            'widget'    => $options['widget'],
        ])
        ->add('entity_proxy', EntityProxyFormType::class, [
            'business_entity_id' => $options['businessEntityId'],
            'namespace'          => $options['namespace'],
            'widget'             => $options['widget'],
        ]);
    }

    /**
     * Add the fields to the form for the query mode.
     *
     * @param FormBuilderInterface|FormInterface $form
     * @param array                              $options
     */
    protected function addQueryFields($form, $options)
    {
        $form->add('query');
        $form->add('fields', WidgetFieldsFormType::class, [
            'label'     => 'widget.form.entity.fields.label',
            'namespace' => $options['namespace'],
            'widget'    => $options['widget'],
        ]);
    }

    /**
     * bind form to WidgetRedactor entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\WidgetBundle\Entity\Widget',
            'translation_domain' => 'victoire',
            'mode'               => Widget::MODE_STATIC,
        ]);

        $resolver->setDefined([
            'widget',
            'filters',
            'slot',
            'namespace',
            'businessEntityId',
        ]);
    }
}
