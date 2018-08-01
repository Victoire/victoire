<?php

namespace Victoire\Bundle\CoreBundle\Annotations;

/**
 * Marks a field with text sementical behavior.
 *
 * @Annotation
 **/
class ReceiverProperty
{
    private $types = [];
    private $required = false;

    /**
     * define supported types.
     *
     * @param array $types supported types (text, media, date)
     **/
    public function __construct($data)
    {
        if (array_key_exists('required', $data)) {
            $this->required = $data['required'];
        }

        if (array_key_exists('value', $data)) {
            if (is_array($data['value']) && count($data['value']) > 1) {
                $this->types = $data['value'];
            } else {
                $this->types = [$data['value']];
            }
        }
    }

    /**
     * Get types.
     *
     * @return null|multitype:NULL
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Is required.
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }
}
