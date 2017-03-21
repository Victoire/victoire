<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Resolver;

use Victoire\Bundle\APIBusinessEntityBundle\Chain\ApiAuthenticationChain;
use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;
use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIEndpoint;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
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
        // build an array with businessIdentifiers properties names
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

        $entity = $this->callApi($businessEntity->getEndpoint(), $getMethod);
        // Then the BusinessEntity is an API result, it's not a proper object but a decoded json result.
        // It has no classname so we cannot resolve the related BusinessEntity definition, so we
        // store the businessEntity object into the entity.
        $entity->_businessEntity = $entityProxy->getBusinessEntity();

        return $entity;
    }

    /**
     * Fetch API to get a list of entities.
     *
     * @param APIBusinessEntity $businessEntity
     *
     * @return array
     */
    public function getBusinessEntities(APIBusinessEntity $businessEntity, $page = 1)
    {
        $data = $this->callApi($businessEntity->getEndpoint(), $businessEntity->getListMethod($page));

        foreach ($data as $entity) {
            $entity->_businessEntity = $businessEntity;
        }
        if (count($data) > 0) {
            $data = array_merge($data, $this->getBusinessEntities($businessEntity, ++$page));
        }

        return $data;
    }

    /**
     * filter API to get a list of entities.
     *
     * @param APIBusinessEntity $businessEntity
     * @param array             $filter
     *
     * @return mixed
     */
    public function searchBusinessEntities(APIBusinessEntity $businessEntity, BusinessProperty $businessProperty, $filter)
    {
        $getMethod = $businessEntity->getListMethod()
            .(false !== strpos($businessEntity->getListMethod(), '?') ? '&' : '?')
            .$businessProperty->getFilterMethod();
        $getMethod = preg_replace('/{{([a-zA-Z]+)}}/', $filter, $getMethod);

        $data = $this->callApi($businessEntity->getEndpoint(), $getMethod);

        foreach ($data as $entity) {
            $entity->businessEntity = $businessEntity;
        }

        return $data;
    }

    /**
     * Sends a curl request to a given path.
     *
     * @param APIEndpoint $endPoint
     * @param string $getMethod
     *
     * @return array
     */
    protected function callApi(APIEndpoint $endPoint, $getMethod)
    {
        $host = $endPoint->getHost();
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
