<?php

namespace Victoire\Bundle\ViewReferenceBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class ArrayToViewReferenceTransformer implements DataTransformerInterface
{
    public $className = 'Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference';

    public function __construct()
    {
        $refClass = new \ReflectionClass($this->className);
        $this->properties = $refClass->getProperties();
    }

    /**
     * Array to ViewReference
     * {@inheritdoc}
     *
     * @param array $array
     *
     * @return ViewReference
     */
    public function transform($array)
    {
        $className = $this->className;
        $viewReference = new $className();
        foreach ($array as $prop => $value) {
            $methodName = 'set'.ucfirst($prop);
            $viewReference->$methodName((string) $value);
        }

        return $viewReference;
    }

    /**
     * View Reference to array
     * {@inheritdoc}
     *
     * @param ViewReference $viewReference
     *
     * @return array
     */
    public function reverseTransform($viewReference)
    {
        $array = [];
        foreach ($this->properties as $prop) {
            $methodName = 'get'.ucfirst($prop->getName());
            $array[$prop->getName()] = $viewReference->$methodName();
        }

        return $array;
    }
}
