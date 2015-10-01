<?php

namespace Victoire\Bundle\WidgetBundle\Twig;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\PageBundle\Helper\PageHelper;

/**
 * Twig extension for rendering a link.
 */
class LinkExtension extends \Twig_Extension
{
    protected $router;
    protected $analytics;
    protected $businessEntityHelper; // @victoire_business_page.business_entity_helper
    protected $BusinessPageHelper; // @victoire_business_page.business_page_helper
    protected $pageHelper;
    protected $em; // @doctrine.orm.entity_manager

    public function __construct(
        Router $router,
        RequestStack $requestStack,
        $analytics,
        BusinessEntityHelper $businessEntityHelper,
        BusinessPageHelper $BusinessPageHelper,
        PageHelper $pageHelper,
        EntityManager $em
    ) {
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
        $this->analytics = $analytics;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->BusinessPageHelper = $BusinessPageHelper;
        $this->pageHelper = $pageHelper;
        $this->em = $em;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return \Twig_SimpleFunction[] An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('vic_link_url', [$this, 'victoireLinkUrl']),
            new \Twig_SimpleFunction('vic_link', [$this, 'victoireLink'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('vic_menu_link', [$this, 'victoireMenuLink'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('vic_business_link', [$this, 'victoireBusinessLink'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generate the complete link (with the a tag).
     *
     * @param array  $parameters   The link parameters (go to LinkTrait to have the list)
     * @param string $avoidRefresh Do we have to refresh or not ?
     * @param array  $url          Fallback url
     *
     * @return string
     */
    public function victoireLinkUrl($parameters, $avoidRefresh = true, $url = '#')
    {
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
        extract($parameters); //will assign $linkType, $attachedWidget, $routeParameters, $route, $page, $analyticsTrackCode
        switch ($linkType) {
            case 'viewReference':
                if (is_array($viewReference)) {
                    $viewReference = $viewReference['id'];
                }

                $page = $this->pageHelper->findPageByParameters(['id' => $viewReference]);
                $linkUrl = $this->router->generate('victoire_core_page_show', ['_locale' => $page->getLocale(), 'url' => $page->getUrl()], $referenceType);
                if ($this->request->getRequestUri() != $linkUrl || !$avoidRefresh) {
                    $url = $linkUrl;
                }

                break;
            case 'route':
                $url = $this->router->generate($route, $routeParameters, $referenceType);
                break;
            case 'attachedWidget':
                //fallback when a widget is deleted cascading the relation as null (widget_id = null)
                if ($attachedWidget && method_exists($attachedWidget->getView(), 'getUrl')) {

                    //create base url
                    $url = $this->router->generate('victoire_core_page_show', ['_locale' => $attachedWidget->getView()->getLocale(), 'url' => $attachedWidget->getView()->getUrl()], $referenceType);

                    //If widget in the same view
                    if (rtrim($this->request->getRequestUri(), '/') == rtrim($url, '/')) {
                        $url = '';
                    }
                    //Add anchor part
                    $url .= '#vic-widget-'.$attachedWidget->getId().'-container-anchor';
                }
                break;
        }

        return $url;
    }

    /**
     * Generate the complete link (with the a tag).
     *
     * @param array  $parameters The link parameters (go to LinkTrait to have the list)
     * @param string $label      link label
     * @param array  $attr       custom attributes
     *
     * @return string
     */
    public function victoireLink($parameters, $label, $attr = [], $currentClass = 'active', $url = '#')
    {
        $referenceLink = UrlGeneratorInterface::ABSOLUTE_PATH;
        extract($parameters); //will assign $linkType, $attachedWidget, $routeParameters, $route, $page, $analyticsTrackCode

        if ($linkType == 'attachedWidget' && $attachedWidget && method_exists($attachedWidget->getView(), 'getUrl')) {
            $viewUrl = $this->router->generate('victoire_core_page_show', ['_locale' => $attachedWidget->getView()->getLocale(), 'url' => $attachedWidget->getView()->getUrl()], $referenceLink);
            if (rtrim($this->request->getRequestUri(), '/') == rtrim($viewUrl, '/')) {
                $attr['data-scroll'] = 'smooth';
            }
        }

        //Avoid to refresh page if not needed
        if ($this->request->getRequestUri() == $this->victoireLinkUrl($parameters, false)) {
            $this->addAttr('class', $currentClass, $attr);
        }

        //Build the target attribute
        if ($target == 'ajax-modal') {
            $attr['data-toggle'] = 'ajax-modal';
        } elseif ($target == '') {
            $attr['target'] = '_parent';
        } else {
            $attr['target'] = $target;
        }

        //Add the analytics tracking code attribute
        if (isset($analyticsTrackCode)) {
            $this->addAttr('onclick', $analyticsTrackCode, $attr);
        }

        //Assemble and prepare attributes
        $attributes = [];
        foreach ($attr as $key => $_attr) {
            if (is_array($_attr)) {
                $attr = implode($_attr, ' ');
            } else {
                $attr = $_attr;
            }
            $attributes[] = $key.'="'.$attr.'"';
        }

        $url = $this->victoireLinkUrl($parameters, true, $url);
        //Creates a new twig environment
        $twigEnv = new \Twig_Environment(new \Twig_Loader_String());

        return $twigEnv->render('{{ link|raw }}', ['link' => '<a href="'.$url.'" '.implode($attributes, ' ').'>'.$label.'</a>']);
    }

    /**
     * Generate the complete menu link item (with the li tag).
     *
     * @param array  $parameters The link parameters (go to LinkTrait to have the list)
     * @param string $label      link label
     * @param array  $attr       custom attributes
     *
     * @return string
     */
    public function victoireMenuLink($parameters, $label, $attr = [])
    {
        $linkAttr = [];
        //is the link is active
        if ($this->request->getRequestUri() == $this->victoireLinkUrl($parameters, false)) {
            if (!isset($attr['class'])) {
                $linkAttr['class'] = '';
            }
            $linkAttr['class'] .= 'active'; //avoid to refresh page when not needed
        }

        $linkAttributes = [];
        foreach ($linkAttr as $key => $_attr) {
            if (is_array($_attr)) {
                $linkAttr = implode($_attr, ' ');
            } else {
                $linkAttr = $_attr;
            }
            $linkAttributes[] = $key.'="'.$linkAttr.'"';
        }

        return '<li '.implode($linkAttributes, ' ').'>'.$this->victoireLink($parameters, $label, $attr, false, '#top').'</li>';
    }

    public function victoireBusinessLink($businessEntityInstance, $patternId = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if (!$patternId) {
            $patternId = $this->BusinessPageHelper
                ->guessBestPatternIdForEntity(new \ReflectionClass($businessEntityInstance), $businessEntityInstance->getId(), $this->em);
        }

        $page = $this->pageHelper->findPageByParameters([
            'patternId' => $patternId,
            'entityId'  => $businessEntityInstance->getId(),
        ]);

        $parameters = [
            'linkType'        => 'route',
            'route'           => 'victoire_core_page_show',
            'routeParameters' => [
                'url' => $page->getUrl(),
            ],
            'referenceType' => $referenceType,
        ];

        return $this->victoireLinkUrl($parameters);
    }

    /**
     * Add a given attribute to given attributes.
     *
     * @param string $label
     * @param string $value
     * @param array  $attr  The current attributes array
     *
     * @return LinkExtension
     **/
    protected function addAttr($label, $value, &$attr)
    {
        if (!isset($attr[$label])) {
            $attr[$label] = '';
        } else {
            $attr[$label] .= ' ';
        }
        $attr[$label] .= $value;

        return $this;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'victoire_link_extention';
    }
}
