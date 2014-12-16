<?php

namespace Victoire\Bundle\CoreBundle\Form\Builder;

use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;

/**
 * Edit Page Type
 * @author lenybernard
 */
class EntityProxyFieldsBuilder
{
    private $cacheReader;

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function __construct(BusinessEntityCacheReader $cacheReader)
    {
        $this->cacheReader = $cacheReader;
    }

    /**
     * Build
     * @param FormBuilderInterface $builder
     * @param string               $namespace
     *
     * @return array The all list of fields type to add for the entity namespace given
     */
    public function buildForEntityAndWidgetType(&$builder, $widgetType, $namespace)
    {
        //Try to add a new form for each entity with the correct annotation and business properties
        $businessProperties = $this->cacheReader->getBusinessProperties($namespace);
        $receiverProperties = $this->cacheReader->getReceiverProperties();

        if (!empty($receiverProperties[$widgetType])) {
            foreach ($receiverProperties[$widgetType] as $_fields) {
                foreach ($_fields as $fieldKey => $fieldVal) {
                    //Check if entity has all the required receiver properties as business properties
                    if (isset($businessProperties[$key]) && is_array($businessProperties[$key]) && count($businessProperties[$key])) {
                        //Create form types with field as key and values as choices
                        $builder->add($fieldKey, 'choice', array(
                            'choices' => $businessProperties[$key]
                        ));
                    } else {
                        throw new \Exception(sprintf('The Entity %s doesn\'t have a %s property, which is required by %s widget', $namespace, $key, $widgetType));
                    }
                }
            }
        }
    }
}
