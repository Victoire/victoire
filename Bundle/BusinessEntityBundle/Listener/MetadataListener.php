<?php

namespace Victoire\Bundle\BusinessEntityBundle\Listener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Victoire\Bundle\BusinessEntityBundle\Doctrine\MetadataBuilder;

class MetadataListener
{
    private $metadataBuilder;

    public function __construct(MetadataBuilder $metadataBuilder)
    {
        $this->metadataBuilder = $metadataBuilder;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $this->metadataBuilder->loadMetadataForClass($args->getClassMetadata()->name, $args->getClassMetadata());
    }
}
