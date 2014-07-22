<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Controller;

use Victoire\Bundle\PageBundle\Controller\PageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Business entity template controller
 *
 * @Route("/victoire-dcms/business-entity-template")
 */
class BusinessEntityController extends PageController
{
    /**
     *
     * @Route("/", name="victoire_businessentitytemplate_businessentity_index")
     *
     * @return template
     *
     */
    public function indexAction()
    {
        $businessEntityManager = $this->get('victoire_core.helper.business_entity_helper');

        //services
        $em = $this->get('doctrine.orm.entity_manager');

        //the repository
        $repository = $em->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate');

        $businessEntitiesTemplates = array();

        $businessEntities = $businessEntityManager->getBusinessEntities();

        foreach ($businessEntities as $businessEntity) {
            $name = $businessEntity->getName();

            //retrieve the templates
            $templates = $repository->findTemplatesByBusinessEntity($businessEntity);

            $businessEntitiesTemplates[$name] = $templates;
        }

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityTemplateBundle:BusinessEntity:index.html.twig',
                array('businessEntities' => $businessEntities,
                    'businessEntitiesTemplates' => $businessEntitiesTemplates
                )
            ),
            'success' => true
        ));
    }
}
