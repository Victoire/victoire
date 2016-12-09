<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Resolver;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Resolver\BusinessEntityResolverInterface;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;

/**
 * Class APIBusinessEntityResolver.
 */
class APIBusinessEntityResolver implements BusinessEntityResolverInterface
{

    public function getBusinessEntity(EntityProxy $entityProxy)
    {
        /** @var APIBusinessEntity $businessEntity */
        $businessEntity = $entityProxy->getBusinessEntity();
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, sprintf("%s/%s/%s", $businessEntity->getEndpoint()->getHost(), $businessEntity->getResource(), $entityProxy->getRessourceId()));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return json_decode($result);
    }
}
