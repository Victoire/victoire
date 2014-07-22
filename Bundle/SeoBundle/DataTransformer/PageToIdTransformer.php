<?php

namespace Victoire\Bundle\SeoBundle\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 *
 * @author Paul Andrieux
 *
 */
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
     * Transforms an object (Page) to a string (id).
     *
     * @param  Page|null $page
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
     * Transforms a string (id) to an object (Page).
     *
     * @param  string $id
     * @return Page|null
     * @throws TransformationFailedException if object (Page) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $page = $this->om
            ->getRepository('VictoirePageBundle:Page')
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
