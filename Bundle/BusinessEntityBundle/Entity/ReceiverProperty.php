<?php

namespace Victoire\Bundle\BusinessEntityBundle\Entity;

/**
 * ReceiverProperty.
 */
class ReceiverProperty
{
    protected $fieldName = null;
    protected $required = false;

    /**
     * @return null
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param null $fieldName
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * setState (convert from array to object).
     *
     * @return string
     */
    public static function __set_state($array)
    {
        $receiverProperty = new self();
        $receiverProperty->setFieldName($array['fieldName']);
        $receiverProperty->setRequired($array['required']);

        return $receiverProperty;
    }
}
