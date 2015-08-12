<?php
namespace Victoire\Bundle\BusinessEntityPageBundle\Controller;

use Victoire\Bundle\PageBundle\Controller\PageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * business entity page pattern controller
 *
 * @Route("/victoire-dcms/business-entity-page-pattern")
 */
class BusinessEntityController extends PageController
{
    /**
     * List all business entity page pattern
     * @Route("/", name="victoire_businessentitypage_businessentity_index")
     *
     * @return Json
     *
     */
    public function indexAction()
    {
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');

        //services
        $em = $this->get('doctrine.orm.entity_manager');

        //the repository
        $repository = $em->getRepository('VictoireBusinessEntityPageBundle:BusinessEntityPagePattern');

        $businessEntityPagePatterns = array();

        $businessEntities = $businessEntityHelper->getBusinessEntities();

        foreach ($businessEntities as $businessEntity) {
            $name = $businessEntity->getName();

            //retrieve the pagePatterns
            $pagePatterns = $repository->findPagePatternByBusinessEntity($businessEntity);

            $businessEntityPagePatterns[$name] = $pagePatterns;
        }

        return new JsonResponse(array(
            'html'    => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityPageBundle:BusinessEntity:index.html.twig',
                array(
                    'businessEntities'           => $businessEntities,
                    'businessEntityPagePatterns' => $businessEntityPagePatterns
                )
            ),
            'success' => true
        ));
    }
}
