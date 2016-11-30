<?php

namespace Victoire\Bundle\BusinessEntityBundle\Converter;

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
    public function setBusinessPropertyInstance($string, BusinessProperty $businessProperty, $entity)
    {
        //test parameters
        if ($entity === null) {
            throw new \Exception('The parameter entity can not be null');
        }

        //the attribute to set
        $entityProperty = $businessProperty->getName();

        //the string to replace
        $stringToReplace = '{{item.'.$entityProperty.'}}';

        //the value of the attribute
        $attributeValue = $entity->getEntityAttributeValue($entityProperty);

        //we provide a default value
        if ($attributeValue === null) {
            $attributeValue = '';
        }

        //we replace the string
        $string = str_replace($stringToReplace, $attributeValue, $string);

        return $string;
    }
}
