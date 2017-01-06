<?php

namespace Victoire\Bundle\BusinessEntityBundle\Helper;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntityRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;
use Victoire\Bundle\CoreBundle\Cache\Builder\CacheBuilder;
use Victoire\Bundle\ORMBusinessEntityBundle\Entity\ORMBusinessEntityRepository;

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
     * @var ORMBusinessEntityRepository
     */
    private $ormBusinessEntityRepository;
    /**
     * @var BusinessEntityCacheReader
     */
    private $cacheReader;

    /**
     * Constructor.
     *
     * @param EntityRepository|BusinessEntityRepository $businessEntityRepository
     * @param EntityRepository                          $ormBusinessEntityRepository
     * @param BusinessEntityCacheReader                 $cacheReader
     *
     * @internal param BusinessEntityCacheReader $reader
     * @internal param CacheBuilder $builder
     */
    public function __construct(EntityRepository $businessEntityRepository, EntityRepository $ormBusinessEntityRepository, BusinessEntityCacheReader $cacheReader)
    {
        $this->ormBusinessEntityRepository = $ormBusinessEntityRepository;
        $this->businessEntityRepository = $businessEntityRepository;
        $this->cacheReader = $cacheReader;
    }

    /**
     * Get a business entity.q.
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
            $businessEntity = $this->ormBusinessEntityRepository->findOneBy(['class' => $class->name]);
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
        return $this->ormBusinessEntityRepository->findOneBy(['class' => $classname]);
    }

    public function getAvailableForWidget($widgetName)
    {
        $classes = $this->businessEntityRepository->getByAvailableWidgets($widgetName);

        $receiverProperties = $this->cacheReader->getReceiverProperties($widgetName);
        foreach ($classes as $businessEntity) {
            $businessProperties = $businessEntity->getBusinessProperties();
            foreach ($receiverProperties as $receiverType => $receiverProperty) {
                if (count($businessProperties) > 0) {
                    /** @var BusinessProperty $businessProperty */
                    foreach ($businessProperties as $businessProperty) {
                        if (!in_array($receiverType, $businessProperty->getTypes())) {
                            $businessEntity->setDisable(true);
                        }
                    }
                } else {
                    $businessEntity->setDisable(true);
                }
            }
        }

        return $classes;
    }
}
