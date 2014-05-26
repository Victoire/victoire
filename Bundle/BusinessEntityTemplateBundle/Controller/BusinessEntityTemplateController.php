<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Controller;

use Victoire\Bundle\PageBundle\Controller\BasePageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Business entity template controller
 *
 * @Route("/victoire-dcms/business-entity-template")
 */
class BusinessEntityTemplateController extends BasePageController
{
    /**
     *
     * @Route("/", name="victoire_business_entity_template_index")
     * @Template()
     *
     * @return template
     *
     */
    public function indexAction()
    {
        $businessEntityManager = $this->get('victoire_core.helper.business_entity_helper');

        $businessEntities = $businessEntityManager->getBusinessEntities();

        return array('businessEntities' => $businessEntities);
    }

    /**
     *
     * @Route("/list/{id}", name="victoire_business_entity_template_list")
     * @Template()
     *
     * @return template
     *
     */
    public function listAction($id)
    {
        //services
        $businessEntityManager = $this->get('victoire_core.helper.business_entity_helper');

        //get the businessEntity
        $businessEntity = $businessEntityManager->findById($id);

        //test the result
        if ($businessEntity === null) {
            throw new \Exception('The business entity ['.$id.'] was not found.');
        }

        return array('businessEntity' => $businessEntity);
    }
}
