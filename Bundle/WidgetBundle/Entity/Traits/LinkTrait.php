<?php

namespace Victoire\Bundle\WidgetBundle\Entity\Traits;

use Victoire\Bundle\CoreBundle\Entity\Link;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Link trait adds fields to create a link to a page, widget, url or route.
 *
 * @Assert\Callback(methods={"validateLink"})
 */
trait LinkTrait
{
    /**
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\CoreBundle\Entity\Link", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="link_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    protected $link;

    /**
     * Has link.
     *
     * @return bool
     */
    public function hasLink()
    {
        return $this->link ? true : false;
    }

    /**
     * Get link.
     *
     * @return Link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set link.
     *
     * @param string $link
     *
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }
}
