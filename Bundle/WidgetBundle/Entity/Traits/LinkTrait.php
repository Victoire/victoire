<?php
namespace Victoire\Bundle\WidgetBundle\Entity\Traits;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Link trait adds fields to create a link to a page, widget, url or route
 *
 * @Assert\Callback(methods={"validateLink"})
 */
trait LinkTrait
{
    /**
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\CoreBundle\Entity\Link")
     * @ORM\JoinColumn(name="link_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    protected $link;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     * \ will be removed in next major version
     */
    public $url;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=10, nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     * \ will be removed in next major version
     */
    public $target;

    /**
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\PageBundle\Entity\BasePage")
     * @ORM\JoinColumn(name="attached_page_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     * \ will be removed in next major version
     */
    public $page;

    /**
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\WidgetBundle\Entity\Widget")
     * @ORM\JoinColumn(name="attached_widget_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     * \ will be removed in next major version
     */
    public $attachedWidget;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=55, nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     * \ will be removed in next major version
     */
    public $route;

    /**
     * @var string
     *
     * @ORM\Column(name="route_parameters", type="array", nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     * \ will be removed in next major version
     */
    public $routeParameters = array();

    /**
     * @var string
     *
     * @ORM\Column(name="link_type", type="string", length=255, nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     * \ will be removed in next major version
     */
    public $linkType;

    /**
     * @var string
     *
     * @ORM\Column(name="analytics_track_code", type="text", nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     * \ will be removed in next major version
     */
    public $analyticsTrackCode;

    /**
     * Has link
     *
     * @return boolean
     */
    public function hasLink()
    {
        return $this->link ? true : false;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set link
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
