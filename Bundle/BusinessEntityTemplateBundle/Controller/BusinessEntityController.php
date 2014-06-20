<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Controller;

use Victoire\Bundle\PageBundle\Controller\BasePageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Business entity template controller
 *
 * @Route("/victoire-dcms/business-entity-template")
 */
class BusinessEntityController extends BasePageController
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
        $businessEntityTemplateHelper = $this->get('victoire_business_entity_template.helper.business_entity_template_helper');

        $businessEntitiesTemplates = array();

        $businessEntities = $businessEntityManager->getBusinessEntities();

        foreach ($businessEntities as $businessEntity) {
            $name = $businessEntity->getName();
            $templates = $businessEntityTemplateHelper->findTemplatesByBusinessEntity($businessEntity);
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
