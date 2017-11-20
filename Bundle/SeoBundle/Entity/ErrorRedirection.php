<?php

namespace Victoire\Bundle\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Redirection.
 *
 * @ORM\Entity()
 */
class ErrorRedirection extends Redirection
{
    /**
     * @var HttpError $error
     *
     * @ORM\OneToOne(
     *     targetEntity="Victoire\Bundle\SeoBundle\Entity\HttpError",
     *     mappedBy="redirection"
     * )
     */
    private $error;

    /**
     * @return HttpError
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param HttpError $error
     *
     * @return Redirection
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Simple proxy to Error's url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getError()->getUrl();
    }
}