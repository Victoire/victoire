<?php

namespace Victoire\Bundle\BusinessEntityBundle\Listener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Victoire\Bundle\CoreBundle\Annotations\Driver\AnnotationDriver;

class MetadataListener
{
    private $annotationDriver;

    public function __construct(AnnotationDriver $annotationDriver)
    {
        $this->annotationDriver = $annotationDriver;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $entityManager = $args->getEntityManager();

        $omDriver = $entityManager->getConfiguration()->getMetadataDriverImpl();
        $omDriver->addDriver($this->annotationDriver, $args->getClassMetadata()->name);
    }
}
