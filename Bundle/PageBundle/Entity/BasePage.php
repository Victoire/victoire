<?php
namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Victoire\Bundle\CoreBundle\Entity\Route;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;

/**
 * Page
 *
 * @ORM\Entity
 * @ORM\Table("vic_base_page")
 * @UniqueEntity("url")
 * @ORM\HasLifecycleCallbacks
 */
abstract class BasePage extends View
{
    const STATUS_DRAFT = "draft";
    const STATUS_PUBLISHED = "published";
    const STATUS_UNPUBLISHED = "unpublished";
    const STATUS_SCHEDULED = "scheduled";

    /**
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\SeoBundle\Entity\PageSeo", inversedBy="page", cascade={"persist"})
     * @ORM\JoinColumn(name="seo_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $seo;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\CoreBundle\Entity\Route", mappedBy="page", cascade={"persist", "remove"})
     */
    protected $routes;

    /**
     * @var string
     * This property is computed by the method PageSubscriber::buildUrl
     *
     * @ORM\Column(name="url", type="string", unique=true)
     */
    protected $url;

    /**
     * @ORM\OneToMany(targetEntity="Victoire\Bundle\SeoBundle\Entity\PageSeo", mappedBy="redirectTo")
     */
    protected $referers;

    /**
     * @var boolean
     *
     * Do we compute automatically the url on the flush
     *
     * @ORM\Column(name="compute_url", type="boolean", nullable=false)
     */
    protected $computeUrl = true;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    protected $status = self::STATUS_PUBLISHED;

    /**
    * @var datetime $publishedAt
    *
    * @ORM\Column(name="publishedAt", type="datetime")
    * @VIC\BusinessProperty("date")
    */
    protected $publishedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="undeletable", type="boolean")
     */
    protected $undeletable = false;

    /**
     * to string
     *
     * @return string
     **/
    public function __toString()
    {
        return $this->name;
    }

    /**
     * contruct
     **/
    public function __construct()
    {
        parent::__construct();
        $this->publishedAt = new \DateTime();
    }

    /**
     * Set seo
     * @param PageSeo $seo
     *
     * @return Page
     */
    public function setSeo($seo)
    {
        if ($seo !== null) {
            $seo->setPage($this);
        }

        $this->seo = $seo;

        return $this;
    }

    /**
     * Get seo
     *
     * @return PageSeo
     */
    public function getSeo()
    {
        return $this->seo;
    }

    /**
     * Get referers
     *
     * @return string
     */
    public function getReferers()
    {
        return $this->referers;
    }

    /**
     * Set the refere
     *
     * @param string $referers
     */
    public function setReferers($referers)
    {
        $this->referers = $referers;
    }

    /**
     * Set routes
     *
     * @param routes $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }
    /**
     * Add route
     *
     * @param route $route
     */
    public function addRoute($route)
    {
        $this->routes[] = $route;
    }

    /**
     * Get routes
     *
     * @return routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set url
     *
     * @param url $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set status
     *
     * @param status $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set publishedAt
     *
     * @param publishedAt $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * Get publishedAt
     *
     * @return publishedAt
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Is this page published
     *
     * @return bool is published ?
     */
    public function isPublished()
    {
        if (
            $this->getStatus() === self::STATUS_PUBLISHED ||
            $this->getStatus() === self::STATUS_SCHEDULED &&
            $this->getPublishedAt() < new \DateTime()
            ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set position
     *
     * @param position $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get the compute url value
     *
     * @return boolean The compute url
     */
    public function getComputeUrl()
    {
        return $this->computeUrl;
    }

    /**
     * Set the compute url value
     *
     * @param boolean $computeUrl
     */
    public function setComputeUrl($computeUrl)
    {
        $this->computeUrl = $computeUrl;
    }
}
