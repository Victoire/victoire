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
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\CoreBundle\Entity\Link", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="link_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    protected $link;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     */
    public $url;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=10, nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     */
    public $target;

    /**
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\PageBundle\Entity\BasePage")
     * @ORM\JoinColumn(name="attached_page_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     */
    public $page;

    /**
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\WidgetBundle\Entity\Widget")
     * @ORM\JoinColumn(name="attached_widget_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     */
    public $attachedWidget;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=55, nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     */
    public $route;

    /**
     * @var string
     *
     * @ORM\Column(name="route_parameters", type="array", nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     */
    public $routeParameters = array();

    /**
     * @var string
     *
     * @ORM\Column(name="link_type", type="string", length=255, nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     */
    public $linkType;

    /**
     * @var string
     *
     * @ORM\Column(name="analytics_track_code", type="text", nullable=true)
     * @deprecated please run the victoire:legacy:linkMigrator command
     */
    public $analyticsTrackCode;

    public $linkParameters;

    /**
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @deprecated please run the victoire:legacy:linkMigrator command
     */
    public function getLinkParameters()
    {
        return $this->getLink()->getParameters();
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return LinkTrait
     */
    public function setUrl($url)
    {
        return $this->getLink()->setUrl($url);
    }

    /**
     * Get url
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return string
     */
    public function getUrl()
    {
        return $this->getLink()->getUrl();
    }

    /**
     * Set target
     *
     * @param string $target
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return LinkTrait
     */
    public function setTarget($target)
    {
        return $this->getLink()->setTarget($target);
    }

    /**
     * Get target
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return string
     */
    public function getTarget()
    {
        return $this->getLink()->getTarget();
    }

    /**
     * Set route
     *
     * @param string $route
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return LinkTrait
     */
    public function setRoute($route)
    {
        return $this->getLink()->setRoute($route);
    }

    /**
     * Get route
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return string
     */
    public function getRoute()
    {
        return $this->getLink()->getRoute();
    }

    /**
     * Set routeParameters
     *
     * @param array $routeParameters
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return LinkTrait
     */
    public function setRouteParameters($routeParameters)
    {
        return $this->getLink()->setRouteParameters($routeParameters);
    }

    /**
     * Get routeParameters
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->getLink()->getRouteParameters();
    }

    /**
     * Set page
     * @param \Victoire\Bundle\PageBundle\Entity\Page $page
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return LinkTrait
     */
    public function setPage($page = null)
    {
        return $this->getLink()->setPage($page);
    }

    /**
     * Get page
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return \Victoire\Bundle\PageBundle\Entity\BasePage
     */
    public function getPage()
    {
        return $this->getLink()->getPage();
    }

    /**
     * Set linkType
     *
     * @param string $linkType
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return LinkTrait
     */
    public function setLinkType($linkType)
    {
        return $this->getLink()->setLinkType();
    }

    /**
     * Get linkType
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return string
     */
    public function getLinkType()
    {
        return $this->getLink()->getLinkType();
    }
    /**
     * Get attachedWidget
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return string
     */
    public function getAttachedWidget()
    {
        return $this->getLink()->getAttachedWidget();
    }

    /**
     * Set attachedWidget
     *
     * @param string $attachedWidget
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return $this
     */
    public function setAttachedWidget($attachedWidget)
    {
        return $this->getLink()->setAttachedWidget($attachedWidget);
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
     * @deprecated please run the victoire:legacy:linkMigrator command
     **/
    public function checkLink(ExecutionContextInterface $context)
    {
        $violation = false;
        // check if the name is actually a fake name
        switch ($this->getLink()->getLinkType()) {
            case 'page':
            $violation = $this->getLink()->getPage() == null;
                break;
            case 'route':
            $violation = $this->getLink()->getRoute() == null;
                break;
            case 'url':
            $violation = $this->getLink()->getUrl() == null;
                break;
            case 'attachedWidget':
            $violation = $this->getLink()->getAttachedWidget() == null;
                break;
            default:
                break;
        }

        if ($violation) {
            $context->addViolationAt(
                'firstName',
                'validator.link.error.message.'.$this->getLink()->getLinkType().'Missing',
                array(),
                null
            );
        }
    }

    /**
     * Get analyticsTrackCode
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return string
     */
    public function getAnalyticsTrackCode()
    {
        return $this->getLink()->getAnalyticsTrackCode();
    }

    /**
     * Set analyticsTrackCode
     *
     * @param string $analyticsTrackCode
     *
     *
     * @deprecated please run the victoire:legacy:linkMigrator command
     * @return $this
     */
    public function setAnalyticsTrackCode($analyticsTrackCode)
    {
        return $this->getLink()->setAnalyticsTrackCode($analyticsTrackCode);
    }

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
