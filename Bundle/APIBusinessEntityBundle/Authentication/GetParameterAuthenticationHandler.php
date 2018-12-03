<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Authentication;

use Victoire\Bundle\APIBusinessEntityBundle\Authentication\Interfaces\APIAuthenticationMethodInterface;

class GetParameterAuthenticationHandler implements APIAuthenticationMethodInterface
{
    const TYPE = 'get_parameter';

    /**
     * Concats the get path with the token.
     */
    public function handle($curl, &$getMethod, &$token)
    {
        $getMethod .= $token;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return self::TYPE;
    }
}
