<?php

namespace Victoire\Bundle\BlogBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Victoire\Bundle\BlogBundle\Entity\Tag;

class TagToStringTransformer implements DataTransformerInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * String to Tags.
     *
     * @param mixed $array
     *
     * @internal param mixed $string
     *
     * @return array
     */
    public function reverseTransform($array)
    {
        if (is_array($array) && array_key_exists(0, $array)) {
            $newIds = [];
            $ids = explode(',', $array[0]);
            $repo = $this->em->getRepository('VictoireBlogBundle:Tag');

            $objects = $repo->findById($ids);
            foreach ($objects as $object) {
                if (false !== $key = array_search($object->getId(), $ids)) {
                    $newIds[] = $ids[$key];
                    unset($ids[$key]);
                }
            }

            $objectsArray = [];
            foreach ($ids as $title) {
                if ($title !== '') {
                    $object = new Tag();
                    $object->setTitle($title);
                    $this->em->persist($object);
                    $objectsArray[] = $object;
                }
            }
            $this->em->flush();

            foreach ($objectsArray as $objectObj) {
                $newIds[] = $objectObj->getId();
            }

            return $newIds;
        }

        return $array;
    }

    /**
     * @param mixed $array
     *
     * @return string
     */
    public function transform($array)
    {
        return $array;
    }
}
