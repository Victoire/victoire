<?php

namespace Victoire\Bundle\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class HttpError.
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="code", type="integer")
 * @ORM\DiscriminatorMap({"404" = "Error404"})
 * @ORM\Entity(repositoryClass="Victoire\Bundle\SeoBundle\Repository\HttpErrorRepository")
 * @ORM\Table("vic_http_error")
 */
abstract class HttpError
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", nullable=false, unique=true)
     */
    protected $url;

    /**
     * @var string $errorMessage
     *
     * @ORM\Column(name="error_message", type="string")
     */
    protected $errorMessage;

    /**
     * @var int $counter
     *
     * @ORM\Column(name="counter", type="integer", options={"default": 1})
     */
    protected $counter = 1;

    /**
     * @var ErrorRedirection $redirection
     *
     * @ORM\OneToOne(
     *     targetEntity="Victoire\Bundle\SeoBundle\Entity\ErrorRedirection",
     *     inversedBy="error"
     * )
     * @ORM\JoinColumn(name="redirection_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $redirection;

    public function __construct($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return HttpError
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     *
     * @return HttpError
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * @param int $counter
     *
     * @return HttpError
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * @return HttpError
     */
    public function increaseCounter()
    {
        $this->counter += 1;

        return $this;
    }

    /**
     * @return ErrorRedirection
     */
    public function getRedirection()
    {
        return $this->redirection;
    }

    /**
     * @param ErrorRedirection $redirection
     *
     * @return HttpError
     */
    public function setRedirection($redirection)
    {
        $this->redirection = $redirection;

        return $this;
    }
}