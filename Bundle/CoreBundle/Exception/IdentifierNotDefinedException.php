<?php
/**
 * Created by PhpStorm.
 * User: lenybernard
 * Date: 24/03/2016
 * Time: 21:58
 */

namespace Victoire\Bundle\CoreBundle\Exception;

class IdentifierNotDefinedException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct($identifiers, $message = "", $code = 450, \Exception $previous = null) {
        $message = sprintf(
            'The following identifiers are not defined as well, (%s)
                    you need to add the following lines on your businessEntity properties:
                    <br> <pre>@VIC\BusinessProperty("businessParameter")</pre>',
            implode($identifiers, ', ')
        );
        parent::__construct($message, $code, $previous);
    }
}