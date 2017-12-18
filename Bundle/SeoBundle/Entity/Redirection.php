<?php

namespace Victoire\Bundle\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\CoreBundle\Entity\Link;

/**
 * Redirection.
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\SeoBundle\Repository\RedirectionRepository")
 * @ORM\Table("vic_redirection")
 *
 * @UniqueEntity("url")
 */
class Redirection
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Link
     *
     * @Assert\Valid()
     * @Assert\NotBlank()
     *
     * @ORM\OneToOne(
     *     targetEntity="Victoire\Bundle\CoreBundle\Entity\Link",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(
     *     name="link",
     *     referencedColumnName="id",
     *     onDelete="CASCADE"
     * )
     */
    private $link;

    /**
     * @var int
     *
     * @ORM\Column(name="counter", type="integer", nullable=false)
     */
    private $counter = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", unique=true, nullable=true)
     */
    private $url;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return Redirection
     */
    public function setLink($link)
    {
        $this->link = $link;

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
     * @return Redirection
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * @return $this
     */
    public function increaseCounter()
    {
        $this->counter += 1;

        return $this;
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
     * @return Redirection
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}