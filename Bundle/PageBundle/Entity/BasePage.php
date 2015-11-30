<?php

namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * Page.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\PageBundle\Repository\BasePageRepository")
 * @ORM\Table("vic_base_page")
 * @ORM\HasLifecycleCallbacks
 */
abstract class BasePage extends View implements WebViewInterface
{
    use \Victoire\Bundle\PageBundle\Entity\Traits\WebViewTrait;

    protected $viewReference;

    /**
     * Construct.
     **/
    public function __construct()
    {
        parent::__construct();
        $this->publishedAt = new \DateTime();
        $this->status = PageStatus::PUBLISHED;
        $this->homepage = false;
    }

    /**
     * @param ViewReference $viewReference
     *
     * @return BasePage
     */
    public function setViewReference(ViewReference $viewReference)
    {
        $this->viewReference = $viewReference;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewReference()
    {
        return $this->viewReference;
    }
}
