<?php
namespace Victoire\Bundle\WidgetBundle\Entity\Traits;

/**
 * Link trait adds fields to create a link to a page, widget, url or route
 *
 */
trait LinkTrait
{

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=10)
     */
    protected $target;

    /**
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\PageBundle\Entity\Page")
     * @ORM\JoinColumn(name="attached_page_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     */
    protected $page;

    /**
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\WidgetBundle\Entity\Widget")
     * @ORM\JoinColumn(name="attached_widget_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     */
    protected $attachedWidget;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=55, nullable=true)
     */
    protected $route;

    /**
     * @var string
     *
     * @ORM\Column(name="route_parameters", type="array", nullable=true)
     */
    protected $routeParameters = array();

    /**
     * @var string
     *
     * @ORM\Column(name="link_type", type="string", length=255)
     */
    protected $linkType;

    /**
     * Set url
     *
     * @param string $url
     *
     * @return WidgetButton
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set target
     *
     * @param string $target
     *
     * @return WidgetButton
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set route
     *
     * @param string $route
     *
     * @return WidgetButton
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set routeParameters
     *
     * @param array $routeParameters
     *
     * @return WidgetButton
     */
    public function setRouteParameters($routeParameters)
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    /**
     * Get routeParameters
     *
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * Set page
     * @param \Victoire\Bundle\PageBundle\Entity\Page $page
     *
     * @return WidgetButton
     */
    public function setPage($page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return \Victoire\Bundle\PageBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set linkType
     *
     * @param string $linkType
     *
     * @return MenuItem
     */
    public function setLinkType($linkType)
    {
        $this->linkType = $linkType;

        return $this;
    }

    /**
     * Get linkType
     *
     * @return string
     */
    public function getLinkType()
    {
        return $this->linkType;
    }
    /**
     * Get attachedWidget
     *
     * @return string
     */
    public function getAttachedWidget()
    {
        return $this->attachedWidget;
    }

    /**
     * Set attachedWidget
     *
     * @param string $attachedWidget
     *
     * @return $this
     */
    public function setAttachedWidget($attachedWidget)
    {
        $this->attachedWidget = $attachedWidget;

        return $this;
    }

}
