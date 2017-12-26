<?php

namespace Victoire\Bundle\PageBundle\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Victoire\Bundle\SeoBundle\Entity\Error404;
use Victoire\Bundle\SeoBundle\Entity\ErrorRedirection;
use Victoire\Bundle\SeoBundle\Entity\HttpError;
use Victoire\Bundle\SeoBundle\Entity\Redirection;

/**
 * Class RedirectionHandler.
 */
class RedirectionHandler
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * RedirectionHandler constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Check if the Error and its associated Redirection exists, then increase the counter of the Error|Redirection.
     *
     * @param Redirection|null $redirection
     * @param Error404|null    $error404
     *
     * @throws NoResultException
     *
     * @return Redirection|Error404
     */
    public function handleError($redirection, $error404)
    {
        if ($redirection) {
            $this->increaseCounter($redirection);

            return $redirection;
        } else if ($error404) {
            $this->increaseCounter($error404);

            return $error404;
        }

        throw new NoResultException();
    }

    /**
     * Return error extension type.
     *
     * @param string $extension
     *
     * @return int
     */
    public function handleErrorExtension($extension)
    {
        if ($extension && ($extension !== 'html' || $extension !== 'twig')) {
            return HttpError::TYPE_FILE;
        }

        return HttpError::TYPE_ROUTE;
    }

    /**
     * @param Redirection|Error404 $object
     */
    private function increaseCounter($object)
    {
        $object->increaseCounter();

        $this->entityManager->flush();
    }
}
