<?php
namespace Victoire\Bundle\CoreBundle\Annotations;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Marks a field with text sementical behavior
 *
 * @Annotation
 **/
class BusinessProperty
{
    private $types;

    /**
     * define supported types
     *
     * @param array $types supported types (text, media, date)
     *
     **/
    public function __construct($types = null)
    {
        $this->types = $types;
    }

    /**
     * Get types
     *
     * @return NULL|multitype:NULL
     */
    public function getTypes()
    {
        if (!array_key_exists('value', $this->types)) {
            return null;
        }

        //return an array, no matter one or many types defined
        if (count($this->types['value']) > 1) {
            return $this->types['value'];
        } else {
            return array($this->types['value']);
        }
    }
}
