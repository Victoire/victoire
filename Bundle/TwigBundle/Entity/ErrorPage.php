<?php
namespace Victoire\Bundle\TwigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Error Page
 *
 * @ORM\Entity
 * @UniqueEntity("code")
 */
class ErrorPage extends BasePage
{
    const TYPE = 'error';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="integer", nullable=true, unique=true)
     */
    protected $code;

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param  string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}
