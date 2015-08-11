<?php

namespace Victoire\Bundle\AnalyticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\UserBundle\Model\User;

/**
 * BrowseEvent
 *
 * @ORM\Table("vic_analytics_browse_event")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\AnalyticsBundle\Repository\BrowseEventRepository")
 */
class BrowseEvent
{
    use \Gedmo\Timestampable\Traits\TimestampableEntity;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\Ip
     * @ORM\Column(name="ip", type="string", length=16)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="viewReferenceId", type="string", length=55)
     */
    private $viewReferenceId;

    /**
     * @var string
     *
     * @Assert\Url
     * @ORM\Column(name="referer", type="text", nullable=true)
     */
    private $referer;

    private $author;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return BrowseEvent
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set viewReferenceId
     *
     * @param string $viewReferenceId
     *
     * @return BrowseEvent
     */
    public function setViewReferenceId($viewReferenceId)
    {
        $this->viewReferenceId = $viewReferenceId;

        return $this;
    }

    /**
     * Get viewReferenceId
     *
     * @return string
     */
    public function getViewReferenceId()
    {
        return $this->viewReferenceId;
    }

    /**
     * Get referer
     *
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * Set referer
     * @param string $referer
     *
     * @return $this
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set author
     * @param User $author
     *
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }
}
