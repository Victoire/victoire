<?php
namespace Victoire\Bundle\WidgetBundle\Entity\Traits;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Link trait adds fields to create a link to a page, widget, url or route
 *
 * @Assert\Callback(methods={"validateLink"})
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
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\PageBundle\Entity\BasePage")
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
     * @var string
     *
     * @ORM\Column(name="analytics_track_code", type="text", nullable=true)
     */
    protected $analyticsTrackCode;

    protected $linkParameters;

    public function getLinkParameters()
    {
        return $this->linkParameters = array(
            'linkType'           => $this->linkType,
            'url'                => $this->url,
            'page'               => $this->page,
            'route'              => $this->route,
            'routeParameters'    => $this->routeParameters,
            'attachedWidget'     => $this->attachedWidget,
            'target'             => $this->target,
            'analyticsTrackCode' => $this->analyticsTrackCode,
        );
    }

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
     * @return \Victoire\Bundle\PageBundle\Entity\BasePage
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

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new Assert\Callback('checkLink'));
    }

    /**
     * undocumented function
     *
     * @return void
     * @author
     **/
    public function checkLink(ExecutionContextInterface $context)
    {
        $violation = false;
        // check if the name is actually a fake name
        switch ($this->getLinkType()) {
            case 'page':
            $violation = $this->getPage() == null;
                break;
            case 'route':
            $violation = $this->getRoute() == null;
                break;
            case 'url':
            $violation = $this->getUrl() == null;
                break;
            case 'attachedWidget':
            $violation = $this->getAttachedWidget() == null;
                break;
            default:
                break;
        }

        if ($violation) {
            $context->addViolationAt(
                'firstName',
                'validator.link.error.message.'.$this->getLinkType().'Missing',
                array(),
                null
            );
        }
    }

    /**
     * Get analyticsTrackCode
     *
     * @return string
     */
    public function getAnalyticsTrackCode()
    {
        return $this->analyticsTrackCode;
    }

    /**
     * Set analyticsTrackCode
     *
     * @param string $analyticsTrackCode
     *
     * @return $this
     */
    public function setAnalyticsTrackCode($analyticsTrackCode)
    {
        $this->analyticsTrackCode = $analyticsTrackCode;

        return $this;
    }
}
