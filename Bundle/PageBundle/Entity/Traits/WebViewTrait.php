<?php

namespace Victoire\Bundle\PageBundle\Entity\Traits;

use Doctrine\Common\Util\ClassUtils;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\PageBundle\Entity\PageStatus;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;

/**
 * This trait make a view displayable for public.
 */
trait WebViewTrait
{
    /**
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\SeoBundle\Entity\PageSeo", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="seo_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $seo;

    /**
     * @var string
     *             This property is computed by the method PageSubscriber::buildUrl
     */
    protected $url;

    /**
     * @ORM\OneToMany(targetEntity="Victoire\Bundle\SeoBundle\Entity\PageSeo", mappedBy="redirectTo")
     */
    protected $referers;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    protected $status = PageStatus::PUBLISHED;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="publishedAt", type="datetime")
     * @VIC\BusinessProperty("date")
     */
    protected $publishedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="homepage", type="boolean", nullable=false)
     */
    protected $homepage;

    /**
     * Set seo.
     *
     * @param PageSeo $seo
     *
     * @return WebViewTrait
     */
    public function setSeo(PageSeo $seo)
    {
        $this->seo = $seo;

        return $this;
    }

    /**
     * Get seo.
     *
     * @return PageSeo
     */
    public function getSeo()
    {
        return $this->seo;
    }

    /**
     * Get referers.
     *
     * @return string
     */
    public function getReferers()
    {
        return $this->referers;
    }

    /**
     * Set the refere.
     *
     * @param string $referers
     */
    public function setReferers($referers)
    {
        $this->referers = $referers;
    }

    /**
     * Set url.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url.
     * Be careful with this method, it's heavy. Prefer the use of the ViewReference->getUrl() as long as possible.
     *
     * @return string
     */
    public function getUrl($useViewReference = true)
    {
        if (true === $useViewReference) {
            if (null !== $this->getReference()) {
                return $this->getReference()->getUrl();
            } else {
                throw new \Exception(
                    sprintf(
                        'No reference is attached to this view for now [#%s, "%s","%s"]',
                        $this->getId(),
                        ClassUtils::getClass($this),
                        $this->getName()
                    )
                );
            }
        } else {
            $urlBuilder = new UrlBuilder();

            /* @var WebViewInterface $this */
            return $urlBuilder->buildUrl($this);
        }
    }

    /**
     * Set status.
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set publishedAt.
     *
     * @param \DateTime $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * Get publishedAt.
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Is this page published.
     *
     * @return bool
     */
    public function isPublished()
    {
        if (
            $this->getStatus() === PageStatus::PUBLISHED ||
            ($this->getStatus() === PageStatus::SCHEDULED &&
            $this->getPublishedAt() < new \DateTime())
            ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get homepage.
     *
     * @return bool
     */
    public function isHomepage()
    {
        return $this->homepage;
    }

    /**
     * Set homepage.
     *
     * @param bool $homepage
     *
     * @return $this
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;

        return $this;
    }
}
