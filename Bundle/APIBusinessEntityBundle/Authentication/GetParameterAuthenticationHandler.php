<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Authentication;


use Victoire\Bundle\APIBusinessEntityBundle\Authentication\Interfaces\APIAuthenticationMethod;

class GetParameterAuthenticationHandler implements APIAuthenticationMethod
{
    const TYPE = 'get_parameter';

    /**
     * Concats the get path with the token
     */
    public function handle($curl, &$getMethod, &$token)
    {
        $getMethod .= $token;
    }

    /**
     * {@inheritDoc}
     */
    public static function getType()
    {
        return self::TYPE;
    }
}
