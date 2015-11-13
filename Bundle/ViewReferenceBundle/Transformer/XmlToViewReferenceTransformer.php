<?php

namespace Victoire\Bundle\ViewReferenceBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class XmlToViewReferenceTransformer implements DataTransformerInterface
{
    public $className = 'Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference';

    public function __construct() {
        $refClass = new \ReflectionClass($this->className);
        $this->properties = $refClass->getProperties();
    }

    /**
     * SimpleXMLElement to ViewReference
     * @inheritdoc
     * @param \SimpleXMLElement $xml
     *
     * @return ViewReference
     */
    public function transform($xmlElement)
    {
        $className = $this->className;
        $viewReference = new $className;

        foreach ($xmlElement->attributes() as $prop => $value) {
            $methodName = 'set'.ucfirst($prop);
            $viewReference->$methodName((string) $value);
        }

        return $viewReference;
    }

    /**
     * View Reference to SimpleXMLElement
     * @inheritdoc
     * @param ViewReference $viewReference
     *
     * @return \SimpleXMLElement $xml
     */
    public function reverseTransform($viewReference)
    {
        $xml = <<<'XML'
<?xml version='1.0' encoding='UTF-8' ?>
<viewReference/>
XML;

        $element = new \SimpleXMLElement($xml);

        foreach ($this->properties as $prop) {
            $methodName = 'get'.ucfirst($prop->getName());
            $element->addAttribute($prop->getName(), $viewReference->$methodName());
        }

        return $element;
    }
}