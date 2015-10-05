<?php

namespace Victoire\Bundle\CoreBundle\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;

class JsonToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * constructor.
     */
    public function __construct()
    {
    }

    /**
     * Transforms a php array into json array.
     *
     * @param array $array
     *
     * @return json array
     */
    public function transform($array = [])
    {
        return is_array($array) ? json_encode($array) : null;
    }

    /**
     * Transforms a json array into a php array.
     *
     * @param string $json The json array
     *
     * @return string
     */
    public function reverseTransform($json)
    {
        return $json === '' ? [] : json_decode($json, true);
    }
}
