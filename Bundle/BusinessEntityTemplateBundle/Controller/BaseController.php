<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * The base of the controller
 *
 */
class BaseController extends Controller
{
    /**
     *
     * @param string $id The id of the business entity
     *
     * @return template
     *
     * @throws Exception If the business entity was not found
     *
     */
    public function getBusinessEntity($id)
    {
        //services
        $businessEntityManager = $this->get('victoire_core.helper.business_entity_helper');

        //get the businessEntity
        $businessEntity = $businessEntityManager->findById($id);

        //test the result
        if ($businessEntity === null) {
            throw new \Exception('The business entity ['.$id.'] was not found.');
        }

        return $businessEntity;
    }
}
