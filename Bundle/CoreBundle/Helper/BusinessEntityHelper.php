<?php
namespace Victoire\Bundle\CoreBundle\Helper;

use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;
use Victoire\Bundle\CoreBundle\Entity\BusinessEntity;
use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;
use Victoire\Bundle\CoreBundle\Entity\BusinessProperty;


/**
 * The BusinessEntityHelper
 *
 * ref: victoire_core.helper.business_entity_helper
 */
class BusinessEntityHelper
{
    protected $annotationReader = null;
    protected $em = null;
    protected $businessEntities = null;

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
        //generate the business entities on demand
        if ($this->businessEntities === null) {
            $annotationReader = $this->annotationReader;

            $businessEntities = $annotationReader->getBusinessClasses();
            $businessEntitiesObjects = array();

            foreach ($businessEntities as $name => $class) {
                $be = new BusinessEntity();
                $be->setId($name);
                $be->setName($name);
                $be->setClass($class);

                //the business properties of the business entity
                $businessProperties = $annotationReader->getBusinessProperties($class);

                //parse the array of the annotation reader
                foreach ($businessProperties as $type => $properties) {
                    foreach ($properties as $property) {
                        $bp = new BusinessProperty();
                        $bp->setType($type);
                        $bp->setEntityProperty($property);

                        //add the business property to the business entity object
                        $be->addBusinessProperty($bp);
                        unset($bp);
                    }
                }

                $businessEntitiesObjects[] = $be;
            }

            $this->businessEntities = $businessEntitiesObjects;
        }

        return $this->businessEntities;
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
            throw new \Exception('The parameter $id is mandatory');
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
                //business entity was found, there is no need to continue
                continue;
            }
        }

        return $businessEntity;
    }

    /**
     * Get a business entity by its classname
     *
     * @param string $classname
     *
     * @throws \Exception
     *
     * @return BusinessEntity
     */
    public function findByClassname($classname)
    {
        if ($classname === null) {
            throw new \Exception('The parameter $$classname is mandatory');
        }

        //get all the business entities
        $businessEntities = $this->getBusinessEntities();

        //the result
        $businessEntity = null;

        //parse the business entities
        foreach ($businessEntities as $tempBusinessEntity) {
            //look for the same id
            if ($tempBusinessEntity->getClass() === $classname) {
                $businessEntity = $tempBusinessEntity;
                //business entity was found, there is no need to continue
                continue;
            }
        }

        return $businessEntity;
    }

    /**
     * Find a entity by the business entity and the id
     *
     * @param BusinessEntity $businessEntity
     * @param string         $attributeName
     * @param string         $id
     *
     * @return Entity
     */
    public function findEntityByBusinessEntityAndAttribute(BusinessEntity $businessEntity, $attributeName, $attributeValue)
    {
        //retrieve the class of the business entity
        $class = $businessEntity->getClass();

        $em = $this->em;

        //get the repository
        $repo = $em->getRepository($class);

        $functionName = 'findOneBy'.ucfirst($attributeName);

        //get the entity
        $entity = call_user_func(array($repo, $functionName), $attributeValue);

        return $entity;
    }

    /**
     * Get the entity from the page and the id given
     *
     * @param BusinessEntityTemplatePage $page             The page
     * @param string                     $entityIdentifier The identifier for the business entity
     *
     * @throws \Exception
     *
     * @return The entity
     */
    public function getEntityByPageAndBusinessIdentifier(BusinessEntityTemplatePage $page, $entityIdentifier)
    {
        $entity = null;

        $template = $page->getBusinessEntityTemplate();

        $businessEntityName = $template->getBusinessEntityName();

        $businessEntity = $this->findById($businessEntityName);

        //the attribute used for getting the entity instance
        $attributeName = $template->getEntityIdentifier();

        //test the result
        if ($businessEntity === null) {
            throw new \Exception('The business entity ['.$businessEntityName.'] was not found.');
        }

        $entity = $this->findEntityByBusinessEntityAndAttribute($businessEntity, $attributeName, $entityIdentifier);

        //test the result
        if ($entity === null) {
            throw new \Exception('The entity ['.$entityIdentifier.'] was not found.');
        }

        return $entity;
    }
}
