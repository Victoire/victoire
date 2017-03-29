<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Authentication\Interfaces;

interface APIAuthenticationMethodInterface
{
    /**
     * @param $curl
     * @param $getMethod
     * @param $token
     *
     * @return mixed
     */
    public function handle($curl, &$getMethod, &$token);

    /**
     * @return mixed
     */
    public static function getType();
}
