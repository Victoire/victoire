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

        $businessEntities = $businessEntityManager->getBusinessEntities();

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityTemplateBundle:BusinessEntity:index.html.twig',
                array('businessEntities' => $businessEntities)
            ),
            'success' => true
        ));
    }

    /**
     *
     * @Route("/list/{id}", name="victoire_businessentitytemplate_businessentity_list")
     *
     * @param string $id The id of the business entity
     *
     * @return template
     *
     * @throws Exception If the business entity was not found
     *
     */
    public function listAction($id)
    {
        //services
        $businessEntityManager = $this->get('victoire_core.helper.business_entity_helper');
        $businessEntityTemplateHelper = $this->get('victoire_business_entity_template.helper.business_entity_template_helper');

        //get the businessEntity
        $businessEntity = $businessEntityManager->findById($id);

        //test the result
        if ($businessEntity === null) {
            throw new \Exception('The business entity ['.$id.'] was not found.');
        }

        //get the templates associated to the business entity
        $templates = $businessEntityTemplateHelper->findTemplatesByBusinessEntity($businessEntity);

        $parameters = array('businessEntity' => $businessEntity, 'templates' => $templates);

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityTemplateBundle:BusinessEntity:list.html.twig',
                $parameters
            ),
            'success' => true
        ));
    }
}
