<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\Form\Field\BusinessEntityHiddenType;
use Victoire\Bundle\CriteriaBundle\Entity\Criteria;
use Victoire\Bundle\CriteriaBundle\Form\Type\CriteriaType;
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
            if ($options['mode'] === null) {
                throw new \Exception('The mode is mandatory if the business_entity_id is given.');
            }
        }

        switch ($options['mode']) {
            case Widget::MODE_ENTITY:
                $this->addEntityFields($builder, $options, $options['mode']);
                break;
            case Widget::MODE_QUERY:
                $this->addQueryFields($builder, $options);
                break;
            case Widget::MODE_BUSINESS_ENTITY:
                $this->addBusinessEntityFields($builder, $options);
                break;
        }

        //add the mode to the form
        $builder->add('mode', HiddenType::class, [
            'data' => $options['mode'],
        ]);
        $builder->add('asynchronous', AsynchronousType::class, [
                'label'    => 'victoire.widget.type.asynchronous.label',
                'required' => false,
                'attr'     => [
                    'class' => 'vic-col-xs-12',
                ],
            ]);
        $builder->add('theme', HiddenType::class);
        $builder->add('quantum', QuantumType::class, [
            'label'    => 'victoire.widget.type.quantum.label',
            'attr'     => [
                'data-flag' => 'v-quantum-name',
            ],
        ]);

        //add the slot to the form
        $builder->add('slot', HiddenType::class, []);

        $this->addCriteriasFields($builder, $options);
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

                switch ($options['mode']) {
                    case Widget::MODE_ENTITY:
                        $this->addEntityFields($form, $options, $mode);
                        break;
                    case Widget::MODE_QUERY:
                        $this->addQueryFields($form, $options);
                        break;
                    case Widget::MODE_BUSINESS_ENTITY:
                        $this->addBusinessEntityFields($form, $options);
                        break;
                }
            }
        );
    }

    /**
     * Add the criterias fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    protected function addCriteriasFields($builder, $options)
    {
        $builder->add('criterias', CriteriaCollectionType::class, [
            'label'         => 'victoire.widget.type.criterias.label',
            'entry_type'    => CriteriaType::class,
            'required'      => false,
            'entry_options' => [
                'dataSources' => $options['dataSources'],
            ],
        ]);
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $dataSources = $options['dataSources']->getDataSources();
                $widget = $event->getData();
                foreach ($dataSources as $alias => $dataSource) {
                    if (!$widget->hasCriteriaNamed($alias)) {
                        $criteria = new Criteria();
                        $criteria->setName($alias);
                        $widget->addCriteria($criteria);
                    }
                }
            }
        );
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $widget = $event->getData();
                /** @var Criteria $criteria */
                foreach ($widget->getCriterias() as $criteria) {
                    if ($criteria->getValue() === null) {
                        $widget->removeCriteria($criteria);
                    }
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
            'label'            => 'widget.form.entity.fields.label',
            'businessEntityId' => $options['businessEntityId'],
            'widget'           => $options['widget'],
        ]);
    }

    /**
     * Add the fields for the form and the entity mode.
     *
     * @param FormBuilderInterface|FormInterface $form
     * @param array                              $options
     */
    protected function addEntityFields($form, $options, $mode)
    {
        $form
        ->add('fields', WidgetFieldsFormType::class, [
            'label'            => 'widget.form.entity.fields.label',
            'businessEntityId' => $options['businessEntityId'],
            'widget'           => $options['widget'],
        ])
        ->add('entity_proxy', EntityProxyFormType::class, [
            'business_entity_id' => $options['businessEntityId'],
            'widget'             => $options['widget'],
            'mode'               => $mode,
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
            'label'            => 'widget.form.entity.fields.label',
            'businessEntityId' => $options['businessEntityId'],
            'widget'           => $options['widget'],
        ]);

        $form->add('businessEntity', BusinessEntityHiddenType::class, [
            'data' => $options['businessEntityId'],
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
            'slot',
            'businessEntityId',
            'dataSources',
        ]);
    }
}
