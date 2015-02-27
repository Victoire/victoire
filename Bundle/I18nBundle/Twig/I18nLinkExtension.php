<?php

namespace Victoire\Bundle\I18nBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\WidgetBundle\Twig\LinkExtension as BaseLinkExtension;

/**
 * Twig extension for rendering a link.
 *
 */
class I18nLinkExtension extends BaseLinkExtension
{

    protected $localeResolver;

    public function __construct(
        Router $router,
        RequestStack $requestStack,
        $analytics,
        BusinessEntityHelper $businessEntityHelper,
        BusinessEntityPageHelper $businessEntityPageHelper,
        PageHelper $pageHelper,
        LocaleResolver $localeResolver
    )
    {
        parent::__construct($router, $requestStack, $analytics, $businessEntityHelper, $businessEntityPageHelper, $pageHelper);
        $this->localeResolver = $localeResolver;
    }

    /**
     * Generate the complete link (with the a tag)
     * @param array  $parameters   The link parameters (go to LinkTrait to have the list)
     * @param string $avoidRefresh Do we have to refresh or not ?
     * @param array  $url          Fallback url
     *
     * @return string
     */
    public function victoireLinkUrl($parameters, $avoidRefresh = true, $url = "#")
    {
        $locale = $this->request->getLocale();
        extract($parameters); //will assign $linkType, $attachedWidget, $routeParameters, $route, $page, $analyticsTrackCode
        switch ($linkType) {
            case 'page':
                //fallback when a page is deleted cascading the relation as null (page_id = null)
                if ($page instanceof WebViewInterface) {
                    //avoid to refresh page when not needed
                    $linkUrl = $this->router->generate('victoire_core_page_show', array('_locale' => $page->getLocale(), 'url' => $page->getUrl()));
                    if ($this->request->getRequestUri() != $linkUrl || !$avoidRefresh) {
                        $url = $linkUrl;
                    }
                }
                break;
            case 'route':
                $url = $this->router->generate($route, $routeParameters);
                break;
            case 'attachedWidget':
                //fallback when a widget is deleted cascading the relation as null (widget_id = null)
                if ($attachedWidget && method_exists($attachedWidget->getView(), 'getUrl')) {

                    //create base url
                    $url = $this->router->generate('victoire_core_page_show', array('_locale'=> $locale, 'url' => $attachedWidget->getView()->getUrl()));

                    //If widget in the same view
                    if (rtrim($this->request->getRequestUri(), '/') == rtrim($url, '/')) {
                        $url = "";
                    }
                    //Add anchor part
                    $url .= "#vic-widget-".$attachedWidget->getId()."-container-anchor";
                }
                break;
        }

        return $url;
    }

    /**
     * Generate the complete link (with the a tag)
     * @param array  $parameters The link parameters (go to LinkTrait to have the list)
     * @param string $label      link label
     * @param array  $attr       custom attributes
     *
     * @return string
     */
    public function victoireLink($parameters, $label, $attr = array(), $currentClass = 'active', $url = "#")
    {

        $locale = $this->request->getLocale();
        extract($parameters); //will assign $linkType, $attachedWidget, $routeParameters, $route, $page, $analyticsTrackCode

        if ($linkType == 'attachedWidget' && $attachedWidget && method_exists($attachedWidget->getView(), 'getUrl')) {
            $viewUrl = $this->router->generate('victoire_core_page_show', array('_locale' => $locale, 'url' => $attachedWidget->getView()->getUrl()));
            if (rtrim($this->request->getRequestUri(), '/') == rtrim($viewUrl, '/')) {
                $attr["data-scroll"] = "smooth";
            }
        }

        //Avoid to refresh page if not needed
        if ($this->request->getRequestUri() == $this->victoireLinkUrl($parameters, false)) {
            $this->addAttr('class', $currentClass, $attr);
        }

        //Build the target attribute
        if ($target == "ajax-modal") {
            $attr['data-toggle'] = 'ajax-modal';
        } elseif ($target == "") {
            $attr['target'] = '_parent';
        } else {
            $attr['target'] = $target;
        }

        //Add the analytics tracking code attribute
        if (isset($analyticsTrackCode)) {
            $this->addAttr('onclick', $analyticsTrackCode, $attr);
        }

        //Assemble and prepare attributes
        $attributes = array();
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

        return $twigEnv->render('{{ link|raw }}', array('link' => '<a href="'.$url.'" '.implode($attributes, ' ').'>'.$label.'</a>'));
    }
}
