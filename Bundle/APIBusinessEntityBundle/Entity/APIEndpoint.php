<?php

namespace Victoire\Bundle\APIBusinessEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 * API endpoint
 *
 * @ORM\Entity()
 * @ORM\Table("vic_api_endpoint")
 */
class APIEndpoint
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;
    /**
     * @var string
     *
     * @ORM\Column(name="host", type="text")
     */
    protected $host;
    /**
     * @var string
     *
     * @ORM\Column(name="token", type="text", nullable=true)
     */
    protected $token;
    /**
     * @var string
     *
     * @ORM\Column(name="tokenType", type="string", nullable=true)
     */
    protected $tokenType;


    /**
     * Get the id.
     *
     * @return string The id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @param string $tokenType
     */
    public function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;
    }
}
