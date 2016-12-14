<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Resolver;

use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;
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
     * APIBusinessEntityResolver constructor.
     *
     * @param ParameterConverter $parameterConverter
     */
    public function __construct(ParameterConverter $parameterConverter)
    {
        $this->parameterConverter = $parameterConverter;
    }

    /**
     * Fetch API to get a single entity
     * @param EntityProxy $entityProxy
     *
     * @return mixed
     */
    public function getBusinessEntity(EntityProxy $entityProxy)
    {
        /** @var APIBusinessEntity $businessEntity */
        $businessEntity = $entityProxy->getBusinessEntity();
        $matches = [];
        $getMethod = $businessEntity->getGetMethod();
        preg_match_all("/{{([a-zA-Z]+)}}/", $getMethod, $matches);
        foreach ($matches[1] as $match) {
            if (in_array($match, $businessEntity->getBusinessIdentifiers())) {
                $value = $entityProxy->getRessourceId();
            } else {
                $props = $entityProxy->getAdditionnalProperties();
                $value = $props[$match];
            }
            $getMethod = $this->parameterConverter->convert($getMethod, $match, $value);
        }

        $path = sprintf("%s%s", $businessEntity->getEndpoint()->getHost(), $getMethod);

        return $this->callApi($path, $businessEntity->getEndpoint()->getToken());
    }

    /**
     * Fetch API to get a list of entities
     * @param APIBusinessEntity $businessEntity
     *
     * @return mixed
     */
    public function getBusinessEntities(APIBusinessEntity $businessEntity)
    {
        if ($businessEntity->getListMethod()) {
            $path = sprintf("%s/%s", $businessEntity->getEndpoint()->getHost(), $businessEntity->getListMethod());
            return $this->callApi($path, $businessEntity->getEndpoint()->getToken());
        }

        return null;

    }

    /**
     * Sends a curl request to a given path
     * @param $path
     *
     * @return mixed
     */
    protected function callApi($path, $token)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,$path . $token);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return json_decode($result);

    }
}
