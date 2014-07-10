<?php

namespace Victoire\Bundle\BusinessEntityBundle\Converter;

use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: victoire_business_entity.converter.parameter_converter
 */
class ParameterConverter
{
    /**
     * Replace the code string with the value of the entity attribute
     *
     * @param The string       $string
     * @param BusinessProperty $businessProperty
     * @param Object           $entity
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
        $entityProperty = $businessProperty->getEntityProperty();

        //the string to replace
        $stringToReplate = '{{item.'.$entityProperty.'}}';

        //the value of the attribute
        $attributeValue = $this->getEntityAttributeValue($entity, $entityProperty);

        //we provide a default value
        if ($attributeValue === null) {
            $attributeValue = '';
        }

        //we replace the string
        $string = str_replace($stringToReplate, $attributeValue, $string);

        return $string;
    }

    /**
     * Get the content of an attribute of an entity given
     *
     * @param entity $entity
     * @param strin $functionName
     *
     * @return mixed
     */
    protected function getEntityAttributeValue($entity, $field)
    {
        $functionName = 'get'.ucfirst($field);

        $fieldValue = call_user_func(array($entity, $functionName));

        return $fieldValue;
    }
}