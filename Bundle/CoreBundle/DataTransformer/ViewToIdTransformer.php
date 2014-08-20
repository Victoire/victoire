<?php

namespace Victoire\Bundle\CoreBundle\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 *
 * @author Leny Bernard
 *
 */
class ViewToIdTransformer implements DataTransformerInterface
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
     * Transforms an object (View) to a string (id).
     *
     * @param  View|null $view
     * @return string
     */
    public function transform($view)
    {
        if (null === $view) {
            return "";
        }

        return $view->getId();
    }

    /**
     * Transforms a string (id) to an object (View).
     *
     * @param string $id
     *
     * @return View|null
     * @throws TransformationFailedException if object (View) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $view = $this->om
            ->getRepository('VictoireCoreBundle:View')
            ->findOneBy(array('id' => $id));

        if (null === $view) {
            throw new TransformationFailedException(sprintf(
                'View #"%s" not found !',
                $id
            ));
        }

        return $view;
    }
}
