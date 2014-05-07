<?php

namespace Victoire\Bundle\SeoBundle\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\PageBundle\Entity\BasePage;

class PageToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (BasePage) to a string (id).
     *
     * @param  BasePage|null $page
     * @return string
     */
    public function transform($page)
    {
        if (null === $page) {
            return "";
        }

        return $page->getId();
    }

    /**
     * Transforms a string (id) to an object (BasePage).
     *
     * @param  string $id
     * @return BasePage|null
     * @throws TransformationFailedException if object (BasePage) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $page = $this->om
            ->getRepository('VictoirePageBundle:BasePage')
            ->findOneBy(array('id' => $id));

        if (null === $page) {
            throw new TransformationFailedException(sprintf(
                'La page #"%s" est introuvable !',
                $id
            ));
        }

        return $page;
    }
}
