<?php

namespace Victoire\Bundle\ViewReferenceBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\BusinessPageReference;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class ArrayToBusinessPageReferenceTransformer extends ArrayToViewReferenceTransformer
{
    public function __construct() {
        $refClass = new \ReflectionClass('Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference');
        $this->properties = $refClass->getProperties();
    }
}