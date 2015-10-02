<?php

namespace Victoire\Bundle\FormBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Victoire\Bundle\BusinessEntityBundle\Transliterator\Transliterator;

class StringToSlugTransformer implements DataTransformerInterface
{
    /**
     * Transforms a string to a slug .
     */
    public function transform($string)
    {
        return $string;
    }

    /**
     * Transforms a url to a string.
     */
    public function reverseTransform($slug)
    {
        $transliterator = new Transliterator();

        return $transliterator->urlize($slug);
    }
}
