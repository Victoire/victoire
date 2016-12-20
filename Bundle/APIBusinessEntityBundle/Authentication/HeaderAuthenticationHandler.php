<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Authentication;


use Victoire\Bundle\APIBusinessEntityBundle\Authentication\Interfaces\APIAuthenticationMethod;

class HeaderAuthenticationHandler implements APIAuthenticationMethod
{
    const TYPE = 'header';

    /**
     * Adds a http header to the curl request with the token
     */
    public function handle($curl, &$getMethod, &$token)
    {
        curl_setopt($curl, CURLOPT_HTTPHEADER, sprintf('Authorization: %s', $token));
    }

    /**
     * {@inheritDoc}
     */
    public static function getType()
    {
        return self::TYPE;
    }
}
