<?php
namespace Victoire\Bundle\CoreBundle\Helper;

use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;
use Victoire\Bundle\CoreBundle\Entity\BusinessEntity;
use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;


/**
 * The BusinessEntityHelper
 *
 * ref: victoire_core.helper.business_entity_helper
 */
class BusinessEntityHelper
{
    protected $annotationReader = null;
    protected $em = null;

    /**
     * Constructor
     *
     * @param AnnotationReader $annotationReader
     * @param EntityManager    $entityManager
     *
     */
    public function __construct(AnnotationReader $annotationReader, EntityManager $entityManager)
    {
        $this->annotationReader = $annotationReader;
        $this->em = $entityManager;
    }

    /**
     * Get the business entities
     *
     * @return array BusinessEntity
     */
    public function getBusinessEntities()
    {
        $annotationReader = $this->annotationReader;

        $businessEntities = $annotationReader->getBusinessClasses();
        $businessEntitiesObjects = array();

        foreach ($businessEntities as $name => $class) {
            $be = new BusinessEntity();
            $be->setId($name);
            $be->setName($name);
            $be->setClass($class);

            $businessEntitiesObjects[] = $be;
        }

        return $businessEntitiesObjects;
    }

    /**
     * Get a business entity by its id
     *
     * @param string $id
     *
     * @throws \Exception
     *
     * @return BusinessEntity
     */
    public function findById($id)
    {
        if ($id === null) {
            throw new \Exception('The paramerter $id is mandatory');
        }

        //get all the business entities
        $businessEntities = $this->getBusinessEntities();

        //the result
        $businessEntity = null;

        //parse the business entities
        foreach ($businessEntities as $tempBusinessEntity) {
            //look for the same id
            if ($tempBusinessEntity->getId() === $id) {
                $businessEntity = $tempBusinessEntity;
                //business entity was found, there is no need ton continue
                continue;
            }
        }

        return $businessEntity;
    }


    /**
     * Find a entity by the business entity and the id
     *
     * @param BusinessEntity $businessEntity
     * @param unknown $id
     *
     * @return Entity
     */
    public function findEntityByBusinessEntityAndId(BusinessEntity $businessEntity, $id)
    {
        //retrieve the class of the business entity
        $class = $businessEntity->getClass();

        $em = $this->em;

        //get the repository
        $repo = $em->getRepository($class);

        //get the entity
        $entity = $repo->findOneById($id);

        return $entity;
    }

    /**
     * Get the entity from the page and the id given
     *
     * @param BusinessEntityTemplatePage $page
     * @param string $id
     *
     * @throws \Exception
     *
     * @return The entity
     */
    public function getEntityByPageAndId(BusinessEntityTemplatePage $page, $id)
    {
        $entity = null;

        $template = $page->getBusinessEntityTemplate();

        $businessEntityId = $template->getBusinessEntityId();

        $businessEntity = $this->findById($businessEntityId);

        //test the result
        if ($businessEntity === null) {
            throw new \Exception('The business entity ['.$businessEntityId.'] was not found.');
        }

        $entity = $this->findEntityByBusinessEntityAndId($businessEntity, $id);

        //test the result
        if ($entity === null) {
            throw new \Exception('The entity ['.$id.'] was not found.');
        }

        return $entity;
    }
}
