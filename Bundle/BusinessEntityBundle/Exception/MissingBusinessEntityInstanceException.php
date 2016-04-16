<?php

namespace Victoire\Bundle\BusinessEntityBundle\Exception;

/**
 * Triggered when at least one Business entity instance is needed
 */
class MissingBusinessEntityInstanceException extends \Exception
{

    /**
     * MissingBusinessEntityException constructor.
     *
     * @param string $className
     */
    public function __construct($className)
    {
        $this->message = sprintf(
            'There isn\'t any instance of %s but at least one is required. Please create one and retry.',
            $className
        );
    }
}