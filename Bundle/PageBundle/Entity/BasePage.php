<?php
namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * Page
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\PageBundle\Repository\BasePageRepository")
 * @ORM\Table("vic_base_page")
 * @UniqueEntity(fields={"url", "locale"})
 * @ORM\HasLifecycleCallbacks
 */
abstract class BasePage extends View
{
    use \Victoire\Bundle\PageBundle\Entity\Traits\WebViewTrait;

    /**
     * contruct
     **/
    public function __construct()
    {
        parent::__construct();
        $this->publishedAt = new \DateTime();
        $this->status = PageStatus::PUBLISHED;
        $this->homepage = false;
    }
}
