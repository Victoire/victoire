<?php

namespace Victoire\Bundle\CoreBundle\Form\Builder;

use Victoire\Bundle\BusinessEntityBundle\Entity\ReceiverProperty;
use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Edit Page Type
 */
class EntityProxyFieldsBuilder
{
    private $cacheReader;
    private $translator;

    /**
     * define form fields
     */
    public function __construct(BusinessEntityCacheReader $cacheReader, TranslatorInterface $translator)
    {
        $this->cacheReader = $cacheReader;
        $this->translator = $translator;
    }

    /**
     * Build
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param string $widgetName
     * @param string $namespace
     *
     * @throws \Exception
     * @return array The all list of fields type to add for the entity namespace given
     */
    public function buildForEntityAndWidgetType(&$builder, $widgetName, $namespace)
    {
        //Try to add a new form for each entity with the correct annotation and business properties
        $businessProperties = $this->cacheReader->getBusinessProperties($namespace);
        $receiverPropertiesTypes = $this->cacheReader->getReceiverProperties($widgetName);

        if (!empty($receiverPropertiesTypes)) {
            foreach ($receiverPropertiesTypes as $type => $receiverProperties) {
                /* @var ReceiverProperty[] $receiverProperties */
                foreach ($receiverProperties as $receiverProperty) {

                    //Check if entity has all the required receiver properties as business properties
                    if (isset($businessProperties[$type]) && is_array($businessProperties[$type]) && count($businessProperties[$type])) {

                        //Create form types with field as key and values as choices
                        //TODO Add some formatter Class or a buildField method responsible to create this type
                        $label = $this->translator->trans(
                            'widget_' . strtolower($widgetName) . '.form.' . $receiverProperty->getFieldName(
                            ) . '.label',
                            array(),
                            'victoire'
                        );
                        $options = array(
                            'choices' => array_combine($businessProperties[$type], $businessProperties[$type]),
                            'label'   => $label,
                            'attr'    => array(
                                'title' => $label
                            )
                        );
                        $builder->add($receiverProperty->getFieldName(), 'choice', $options);
                    } else if ($receiverProperty->isRequired()) {
                        throw new \Exception(
                            sprintf(
                                'The Entity %s doesn\'t have a %s property, which is required by %s widget',
                                $namespace,
                                $type,
                                $widgetName
                            )
                        );
                    }
                }
            }
        }
    }
}
