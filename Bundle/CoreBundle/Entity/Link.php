<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * Victoire Link.
 *
 * @ORM\Entity
 * @ORM\Table("vic_link")
 */
class Link
{
    use \Gedmo\Timestampable\Traits\TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=10, nullable=true)
     */
    protected $target = '_parent';

    /**
     * @deprecated use $viewReference instead
     *
     * @ORM\ManyToOne(targetEntity="Victoire\Bundle\PageBundle\Entity\BasePage")
     * @ORM\JoinColumn(name="attached_page_id", referencedColumnName="id", onDelete="cascade", nullable=true)
     */
    protected $page;

    /**
     * @ORM\Column(name="view_reference", type="string", length=255, nullable=true)
     */
    protected $viewReference;

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
    protected $routeParameters = [];

    /**
     * @var string
     *
     * @ORM\Column(name="link_type", type="string", length=255, nullable=true)
     */
    protected $linkType = 'none';

    /**
     * @var string
     *
     * @ORM\Column(name="analytics_track_code", type="text", nullable=true)
     */
    protected $analyticsTrackCode;

    protected $parameters;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getParameters()
    {
        return $this->parameters = [
            'linkType'           => $this->linkType,
            'url'                => $this->url,
            'page'               => $this->page,
            'viewReference'      => $this->viewReference,
            'route'              => $this->route,
            'routeParameters'    => $this->routeParameters,
            'attachedWidget'     => $this->attachedWidget,
            'target'             => $this->target,
            'analyticsTrackCode' => $this->analyticsTrackCode,
        ];
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Link
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set target.
     *
     * @param string $target
     *
     * @return Link
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set route.
     *
     * @param string $route
     *
     * @return Link
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route.
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set routeParameters.
     *
     * @param array $routeParameters
     *
     * @return Link
     */
    public function setRouteParameters($routeParameters)
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    /**
     * Get routeParameters.
     *
     * @return string
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * @deprecated use view_reference instead
     * Set page
     *
     * @param \Victoire\Bundle\PageBundle\Entity\Page $page
     *
     * @return Link
     */
    public function setPage($page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @deprecated use view_reference instead
     * Get page
     *
     * @return Page|null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Get viewReference.
     *
     * @return string
     */
    public function getViewReference()
    {
        return $this->viewReference;
    }

    /**
     * Set viewReference.
     *
     * @param string $viewReference
     *
     * @return $this
     */
    public function setViewReference($viewReference)
    {
        $this->viewReference = $viewReference;

        return $this;
    }

    /**
     * Set linkType.
     *
     * @param string $linkType
     *
     * @return Link
     */
    public function setLinkType($linkType)
    {
        $this->linkType = $linkType;

        return $this;
    }

    /**
     * Get linkType.
     *
     * @return string
     */
    public function getLinkType()
    {
        return $this->linkType;
    }

    /**
     * Get attachedWidget.
     *
     * @return string
     */
    public function getAttachedWidget()
    {
        return $this->attachedWidget;
    }

    /**
     * Set attachedWidget.
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
     * undocumented function.
     *
     * @return void
     *
     * @author
     **/
    public function checkLink(ExecutionContextInterface $context)
    {
        $violation = false;
        // check if the name is actually a fake name
        switch ($this->getLinkType()) {
            case 'viewReference':
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
                [],
                null
            );
        }
    }

    /**
     * Get analyticsTrackCode.
     *
     * @return string
     */
    public function getAnalyticsTrackCode()
    {
        return $this->analyticsTrackCode;
    }

    /**
     * Set analyticsTrackCode.
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
