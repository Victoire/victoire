<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Chain;

use Victoire\Bundle\APIBusinessEntityBundle\Authentication\Interfaces\APIAuthenticationMethod;
use Victoire\Bundle\APIBusinessEntityBundle\Chain\Exception\UnknownTokenTypeException;

class ApiAuthenticationChain
{
    protected $authenticationMethods;

    public function __construct()
    {
        $this->authenticationMethods = [];
    }

    /**
     * Finds the authentication handler that match with given token type
     *
     * @param $tokenType
     *
     * @return mixed
     * @throws UnknownTokenTypeException
     */
    public function resolve($tokenType)
    {
        if (!array_key_exists($tokenType, $this->authenticationMethods)) {
            throw new UnknownTokenTypeException();
        }

        return $this->authenticationMethods[$tokenType];
    }

    /**
     * Adds an authentication method
     *
     * @param APIAuthenticationMethod $authenticationMethod
     */
    public function addAuthenticationMethod(APIAuthenticationMethod $authenticationMethod)
    {
        $this->authenticationMethods[$authenticationMethod::getType()] = $authenticationMethod;
    }

    /**
     * get authenticaton methods
     *
     * @return array
     */
    public function getAuthenticationMethods()
    {
        return $this->authenticationMethods;
    }

    /**
     * Get all available authentication methods
     *
     * @return array
     */
    public function getAuthenticationMethodsTypes()
    {
        $types = [];
        foreach ($this->authenticationMethods as $type => $authenticationMethod) {
           $types[] = $type;
        }

        return $types;
    }

    /**
     * @param string $alias
     *
     * @return APIAuthenticationMethod
     */
    public function getAuthenticationMethod($alias)
    {
        return $this->authenticationMethods[$alias];
    }
}
