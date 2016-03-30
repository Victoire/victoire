<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
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
    const TYPE_NONE = 'none';
    const TYPE_VIEW_REFERENCE = 'viewReference';
    const TYPE_ROUTE = 'route';
    const TYPE_URL = 'url';
    const TYPE_WIDGET = 'attachedWidget';

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
     * @ORM\Column(name="locale", type="string", length=255, nullable=true)
     */
    protected $locale = null;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=10, nullable=true)
     */
    protected $target = '_parent';

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
    protected $linkType = self::TYPE_NONE;

    /**
     * @var string
     *
     * @ORM\Column(name="analytics_track_code", type="text", nullable=true)
     */
    protected $analyticsTrackCode;

    protected $parameters;
    protected $viewReferencePage;

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
     * @return Link
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getParameters()
    {
        return $this->parameters = [
            'analyticsTrackCode' => $this->analyticsTrackCode,
            'attachedWidget'     => $this->attachedWidget,
            'linkType'           => $this->linkType,
            'locale'             => $this->locale,
            'route'              => $this->route,
            'routeParameters'    => $this->routeParameters,
            'target'             => $this->target,
            'url'                => $this->url,
            'viewReference'      => $this->viewReference,
            'viewReferencePage'  => $this->viewReferencePage,
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
     * @return Link
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
     * @return Link
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
     * @param ExecutionContextInterface $context
     *
     * @author
     */
    public function checkLink(ExecutionContextInterface $context)
    {
        $violation = false;
        // check if the name is actually a fake name
        switch ($this->getLinkType()) {
            case self::TYPE_VIEW_REFERENCE:
            $violation = $this->getViewReference() == null;
                break;
            case self::TYPE_ROUTE:
            $violation = $this->getRoute() == null;
                break;
            case self::TYPE_URL:
            $violation = $this->getUrl() == null;
                break;
            case self::TYPE_WIDGET:
            $violation = $this->getAttachedWidget() == null;
                break;
            default:
                break;
        }

        if ($violation) {
            $context->buildViolation('validator.link.error.message.'.$this->getLinkType().'Missing')->addViolation();
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
     * @return Link
     */
    public function setAnalyticsTrackCode($analyticsTrackCode)
    {
        $this->analyticsTrackCode = $analyticsTrackCode;

        return $this;
    }

    /**
     * Get viewReferencePage.
     *
     * @return mixed
     */
    public function getViewReferencePage()
    {
        return $this->viewReferencePage;
    }

    /**
     * Set viewReferencePage.
     *
     * @param mixed $viewReferencePage
     *
     * @return Link
     */
    public function setViewReferencePage($viewReferencePage)
    {
        $this->viewReferencePage = $viewReferencePage;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Link
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
