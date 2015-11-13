<?php

namespace Victoire\Bundle\ViewReferenceBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class XmlToBusinessPageReferenceTransformer extends XmlToViewReferenceTransformer
{
    public $className = 'Victoire\Bundle\ViewReferenceBundle\ViewReference\BusinessPageReference';
}