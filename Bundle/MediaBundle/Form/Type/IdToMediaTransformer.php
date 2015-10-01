<?php

namespace Victoire\Bundle\MediaBundle\Form\Type;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Victoire\Bundle\MediaBundle\Entity\Media;

/**
 * IdToMediaTransformer.
 */
class IdToMediaTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CurrentValueContainer
     */
    private $currentValueContainer;

    /**
     * @param ObjectManager         $objectManager         The object manager
     * @param CurrentValueContainer $currentValueContainer The currentvaluecontainer
     */
    public function __construct(ObjectManager $objectManager, CurrentValueContainer $currentValueContainer)
    {
        $this->objectManager = $objectManager;
        $this->currentValueContainer = $currentValueContainer;
    }

    /**
     * @param Media $entity The value in the original representation
     *
     * @throws UnexpectedTypeException   when the argument is not an object
     * @throws \InvalidArgumentException when the parameter is a collection
     *
     * @return mixed The value in the transformed representation
     */
    public function transform($entity)
    {
        if (null === $entity || '' === $entity) {
            return '';
        }

        if (!is_object($entity)) {
            throw new UnexpectedTypeException($entity, 'object');
        }

        if ($entity instanceof Collection) {
            throw new \InvalidArgumentException('Expected an object, but got a collection. Did you forget to pass "multiple=true" to an entity field?');
        }

        $this->currentValueContainer->setCurrentValue($entity);

        return [
            'ent' => $entity,
            'id'  => $entity->getId(),
        ];
    }

    /**
     * @param string $key
     *
     * @throws UnexpectedTypeException       when the parameter is not numeric
     * @throws TransformationFailedException when the media item cannot be loaded/found
     *
     * @return Media
     */
    public function reverseTransform($key)
    {
        if ('' === $key || null === $key) {
            return;
        }

        if (!is_numeric($key)) {
            throw new UnexpectedTypeException($key, 'numeric');
        }

        if (!($entity = $this->objectManager->getRepository('VictoireMediaBundle:Media')->findOneById($key))) {
            throw new TransformationFailedException(sprintf('The entity with key "%s" could not be found', $key));
        }

        $this->currentValueContainer->setCurrentValue($entity);

        return $entity;
    }
}
