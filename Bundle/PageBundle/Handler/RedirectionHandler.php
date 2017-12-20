<?php

namespace Victoire\Bundle\PageBundle\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Victoire\Bundle\SeoBundle\Entity\Error404;
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
     * @param Error404 $error
     *
     * @throws NoResultException
     *
     * @return Error404|Redirection
     */
    public function handleError($error)
    {
        if ($error) {
            $redirection = $error->getRedirection();

            if ($redirection) {
                $redirection->increaseCounter();
                $this->entityManager->flush();
            } else {
                $error->increaseCounter();
                $this->entityManager->flush();

                return $error;
            }

            return $redirection;
        }

        throw new NoResultException();
    }
}
