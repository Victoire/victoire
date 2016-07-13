<?php

namespace Victoire\Bundle\ViewReferenceBundle\Exception;

class ViewReferenceNotFoundException extends \Exception
{
    /**
     * ViewReferenceNotFoundException constructor.
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $parametersAsString = [];
        foreach ($parameters as $key => $value) {
            $parametersAsString[] = $key.': '.$value;
        }
        $message = sprintf('Oh no! Cannot find a viewReference for the given parameters %s', implode(',', $parametersAsString));
        parent::__construct($message, 424);
    }
}
