<?php

namespace Victoire\Bundle\BusinessEntityBundle\Converter;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;

/**
 * Parameter Converter
 * ref: victoire_business_entity.converter.parameter_converter.
 */
class ParameterConverter
{
    /**
     * Replace the code string with the value of the entity attribute.
     *
     * @param string           $string
     * @param BusinessProperty $businessProperty
     * @param object           $entity
     *
     * @throws \Exception
     *
     * @return string The updated string
     */
    public function convertFromEntity($string, BusinessProperty $businessProperty, $entity)
    {
        //test parameters
        if ($entity === null) {
            throw new \Exception('The parameter entity can not be null');
        }
        //the attribute to set
        $entityProperty = $businessProperty->getName();

        //the value of the attribute
        $accessor = new PropertyAccessor();
        $attributeValue = $accessor->getValue($entity, $entityProperty);

        return $this->convert($string, 'item.' . $entityProperty, $attributeValue);

    }
    /**
     * Replace the code string with the value of the entity attribute.
     *
     * @param string           $string
     * @param string           $entityProperty
     * @param string           $attributeValue
     *
     * @throws \Exception
     *
     * @return string The updated string
     */
    public function convert($string, $entityProperty, $attributeValue)
    {
        //the string to replace
        $stringToReplace = '{{'.$entityProperty.'}}';

        //we provide a default value
        if ($attributeValue === null) {
            $attributeValue = '';
        }

        //we replace the string
        return str_replace($stringToReplace, $attributeValue, $string);
    }
}
