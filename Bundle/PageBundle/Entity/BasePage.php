<?php

namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;

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

    /**
     * @var string
     *
     * Could be Template or BusinessTemplate
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\TemplateBundle\Entity\Template", inversedBy="inheritors", cascade={"persist"})
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $template;

    /**
     * contruct.
     **/
    public function __construct()
    {
        parent::__construct();
        $this->publishedAt = new \DateTime();
        $this->status = PageStatus::PUBLISHED;
        $this->homepage = false;
    }
}
