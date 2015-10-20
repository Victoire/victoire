<?php

namespace Victoire\Bundle\ViewReferenceBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class ArrayToViewReferenceTransformer implements DataTransformerInterface
{

    /**
     * Array to ViewReference
     * @inheritdoc
     * @param array $array
     *
     * @return ViewReference
     */
    public function transform($array)
    {
        $viewReference = new ViewReference();
        foreach ($array['viewReference'] as $array) {
            foreach ($array as $prop => $value) {
                $methodName = 'set'.ucfirst($prop);
                if (method_exists($viewReference, $methodName)) {
                    $viewReference->$methodName((string) $value);
                }
            }
        }

        return $viewReference;
    }

    /**
     * View Reference to array
     * @inheritdoc
     * @param ViewReference $viewReference
     *
     * @return array
     */
    public function reverseTransform($viewReference)
    {
        $array = [];
        foreach (ViewReferenceHelper::properties as $prop) {
            $methodName = 'get'.ucfirst($prop);
            if (method_exists($viewReference, $methodName)) {
                $array[$prop] = $viewReference->$methodName;
            }

        }

        return $array;
    }
}