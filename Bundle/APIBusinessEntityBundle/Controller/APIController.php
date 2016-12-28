<?php
namespace Victoire\Bundle\APIBusinessEntityBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 * Api controller.
 *
 * @Route("/victoire-dcms/api")
 */
class APIController extends Controller
{

    /**
     * Method used to change of edit modefetch an api.
     *
     * @Route("/fetch/{businessEntity}", name="victoire_api_fetch", options={"expose"=true})
     *
     * @ParamConverter()
     * @return JsonResponse Empty response
     */
    public function fetchApiAction(Request $request, BusinessEntity $businessEntity)
    {
        $accessor = new PropertyAccessor();
        $businessParameter = $businessEntity->getBusinessPropertiesByType(['textable', 'businessParameter'])->first();
        if (!$businessParameter) {
            throw new \Exception(
                sprintf('The BusinessEntity "%s" has no properties that are both "textable" and "BusinessParameter". it is needed to display a text to choose one',
                $businessEntity->getName())
            );
        }

        $filter = $request->query->get('q');
        $businessEntities = $this->get('victoire_business_entity.resolver.business_entity_resolver')->searchBusinessEntities($businessEntity, $businessParameter, $filter);

        $results = [];
        foreach ($businessEntities as $_businessEntity) {
            $results[] = [
                'id' => $accessor->getValue($_businessEntity, $businessEntity->getBusinessIdentifiers()->first()->getName()),
                'text' => $accessor->getValue($_businessEntity, $businessParameter->getName()),
            ];
        }

        return new JsonResponse($results);
    }
}
