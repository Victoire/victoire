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
}
