<?php

namespace Victoire\Bundle\CoreBundle\Annotations;

/**
 * Marks a field with text sementical behavior.
 *
 * @Annotation
 **/
class BusinessProperty
{
    private $types;

    /**
     * define supported types.
     *
     * @param array $types supported types (text, media, date)
     **/
    public function __construct($types = null)
    {
        $this->types = $types;
    }

    /**
     * Get types.
     *
     * @return null|multitype:NULL
     */
    public function getTypes()
    {
        if (!array_key_exists('value', $this->types)) {
            return;
        }

        //return an array, no matter one or many types defined
        if (count($this->types['value']) > 1) {
            return $this->types['value'];
        } else {
            return [$this->types['value']];
        }
    }
}
