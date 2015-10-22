<?php

namespace Victoire\Bundle\TwigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * Error Page.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\TwigBundle\Repository\ErrorPageRepository")
 * @UniqueEntity(fields={"code", "locale"})
 */
class ErrorPage extends View
{
    const TYPE = 'error';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="integer", nullable=true, unique=true)
     */
    protected $code;

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}
