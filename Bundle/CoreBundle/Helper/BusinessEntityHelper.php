<?php
namespace Victoire\Bundle\CoreBundle\Helper;

use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;
use Victoire\Bundle\CoreBundle\Entity\BusinessEntity;


class BusinessEntityHelper
{
    protected $annotationReader = null;

    /**
     * Constructor
     *
     * @param AnnotationReader $annotationReader
     */
    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
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
}
