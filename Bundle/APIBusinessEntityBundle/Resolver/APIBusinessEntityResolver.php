<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Resolver;

use Victoire\Bundle\APIBusinessEntityBundle\Chain\ApiAuthenticationChain;
use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;
use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIEndpoint;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Resolver\BusinessEntityResolverInterface;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

/**
 * Class APIBusinessEntityResolver.
 */
class APIBusinessEntityResolver implements BusinessEntityResolverInterface
{
    /**
     * @var ParameterConverter
     */
    private $parameterConverter;
    /**
     * @var ApiAuthenticationChain
     */
    private $authenticationChain;

    /**
     * APIBusinessEntityResolver constructor.
     *
     * @param ParameterConverter     $parameterConverter
     * @param ApiAuthenticationChain $authenticationChain
     */
    public function __construct(ParameterConverter $parameterConverter, APIAuthenticationChain $authenticationChain)
    {
        $this->parameterConverter = $parameterConverter;
        $this->authenticationChain = $authenticationChain;
    }

    /**
     * Fetch API to get a single entity.
     *
     * @param EntityProxy $entityProxy
     *
     * @return mixed
     */
    public function getBusinessEntity(EntityProxy $entityProxy)
    {
        /** @var APIBusinessEntity $businessEntity */
        $businessEntity = $entityProxy->getBusinessEntity();
        $getMethod = $businessEntity->getGetMethod();
        preg_match_all('/{{([a-zA-Z]+)}}/', $getMethod, $matches);
        $identifiers = array_map(function($property) {
                return $property->getName();
            },
            $businessEntity->getBusinessIdentifiers()->toArray()
        );
        foreach ($matches[1] as $match) {
            if (in_array($match, $identifiers)) {
                $value = $entityProxy->getRessourceId();
            } else {
                $props = $entityProxy->getAdditionnalProperties();
                $value = $props[$match];
            }
            $getMethod = $this->parameterConverter->convert($getMethod, $match, $value);
        }

        return $this->callApi($businessEntity->getEndpoint()->getHost(), $getMethod, $businessEntity->getEndpoint());
    }

    /**
     * Fetch API to get a list of entities.
     *
     * @param APIBusinessEntity $businessEntity
     *
     * @return mixed
     */
    public function getBusinessEntities(APIBusinessEntity $businessEntity)
    {
        if ($businessEntity->getListMethod()) {
            return $this->callApi($businessEntity->getEndpoint()->getHost(), $businessEntity->getListMethod(), $businessEntity->getEndpoint());
        }
    }

    /**
     * Sends a curl request to a given path.
     *
     * @param APIEndpoint $endPoint
     *
     * @return mixed
     */
    protected function callApi($host, $getMethod, APIEndpoint $endPoint)
    {
        $token = $endPoint->getToken();
        $curl = curl_init();
        if ($tokenType = $endPoint->getTokenType()) {
            $this->authenticationChain->resolve($tokenType)->handle($curl, $getMethod, $token);
        }
        curl_setopt($curl, CURLOPT_URL, $host.$getMethod);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return json_decode($result);
    }
}
