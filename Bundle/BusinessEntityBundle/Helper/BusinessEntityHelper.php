<?php

namespace Victoire\Bundle\BusinessEntityBundle\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Cache\Builder\CacheBuilder;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * The BusinessEntityHelper.
 *
 * ref: victoire_core.helper.business_entity_helper
 */
class BusinessEntityHelper
{
    /**
     * @var BusinessEntityRepository
     */
    private $businessEntityRepository;

    /**
     * Constructor.
     *
     * @param BusinessEntityRepository $businessEntityRepository
     *
     * @internal param BusinessEntityCacheReader $reader
     * @internal param CacheBuilder $builder
     */
    public function __construct(EntityRepository $businessEntityRepository)
    {
        $this->businessEntityRepository = $businessEntityRepository;
    }


    /**
     * Get a business entity.q
     *
     * @param mixed $entity
     *
     * @return BusinessEntity
     */
    public function findByEntityInstance($entity)
    {
        $businessEntity = null;
        $class = new \ReflectionClass($entity);
        while (!$businessEntity && $class && $class->name !== null) {
            $businessEntity = $this->businessEntityRepository->findOneBy(['class' => $class->name]);
            $class = $class->getParentClass();
        }

        return $businessEntity;
    }

    /**
     * Get a business entity by classname.
     *
     * @param string $classname
     *
     * @return BusinessEntity
     */
    public function findByEntityClassname($classname)
    {
        return $this->businessEntityRepository->findOneBy(['class' => $classname]);
    }

}
