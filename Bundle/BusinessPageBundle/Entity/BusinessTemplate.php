<?php

namespace Victoire\Bundle\BusinessPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\PageStatus;
use Victoire\Bundle\QueryBundle\Entity\QueryTrait;
use Victoire\Bundle\QueryBundle\Entity\VictoireQueryInterface;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;
use Victoire\Bundle\TemplateBundle\Entity\Template;

/**
 * BusinessTemplate.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessPageBundle\Repository\BusinessTemplateRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BusinessTemplate extends Template implements VictoireQueryInterface
{
    //This trait add the query and business_entity_id columns
    use QueryTrait;

    const TYPE = 'business_template';

    /**
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\SeoBundle\Entity\PageSeo", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="seo_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $seo;

    /**
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage", mappedBy="template", cascade={"remove"})
     */
    protected $inheritors;

    /**
     * @var string
     *
     * @ORM\Column(name="backendName", type="string", length=255)
     */
    protected $backendName;

    /**
     * contruct.
     **/
    public function __construct()
    {
        parent::__construct();
        $this->publishedAt = new \DateTime();
        $this->status = PageStatus::PUBLISHED;
    }

    /**
     * @return [BusinessPage]
     */
    public function getInstances()
    {
        return $this->inheritors;
    }

    public function setInstances($inheritors)
    {
        $this->inheritors = $inheritors;

        return $this;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout ? $this->layout : $this->getTemplate()->getLayout();
    }

    /**
     * Set seo.
     *
     * @param PageSeo $seo
     *
     * @return BusinessTemplate
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
     * @return string
     */
    public function getBackendName()
    {
        return $this->backendName;
    }

    /**
     * @param string $backendName
     *
     * @return $this
     */
    public function setBackendName($backendName)
    {
        $this->backendName = $backendName;

        return $this;
    }

    /**
     * Get inheritors (all Templates having this object as Template).
     *
     * @return [Template]
     */
    public function getTemplateInheritors()
    {
        $templateInheritors = [];
        foreach ($this->inheritors as $inheritor) {
            if ($inheritor instanceof self) {
                $templateInheritors[] = $inheritor;
            }
        }

        return $templateInheritors;
    }
}
