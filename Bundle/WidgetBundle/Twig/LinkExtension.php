<?php

namespace Victoire\Bundle\WidgetBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Twig extension for rendering a link.
 *
 */
class LinkExtension extends \Twig_Extension
{
    private $router;

    public function __construct(Router $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
    }
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('vic_link_url', array($this, 'victoireLinkUrl')),
            new \Twig_SimpleFunction('vic_link', array($this, 'victoireLink'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('vic_menu_link', array($this, 'victoireMenuLink'), array('is_safe' => array('html'))),
        );
    }

    public function victoireLinkUrl($parameters, $avoidRefresh = true, $avoidUrl = "#")
    {
        extract($parameters);
        switch ($linkType) {
            case 'page':
                //fallback when a page is deleted cascading the relation as null (page_id = null)
                if (!$page) {
                    $url = '#';
                    break;
                }
                $url = $this->router->generate('victoire_core_page_show', array('url' => $page->getUrl() ));
                if ($this->request->getRequestUri() == $url && $avoidRefresh) {
                    $url = $avoidUrl; //avoid to refresh page when not needed
                }
                break;
            case 'route':
                $url = $this->router->generate($route, $routeParameters);
                break;
            case 'attachedWidget':
                //fallback when a widget is deleted cascading the relation as null (widget_id = null)
                if (!$attachedWidget || !method_exists($attachedWidget->getView(), 'getUrl')) {
                    $url = '#';
                    break;
                }

                //create base url
                $url = $this->router->generate('victoire_core_page_show', array('url' => $attachedWidget->getView()->getUrl()));

                if (rtrim($this->request->getRequestUri(), '/') == rtrim($url, '/')) {
                    $url = "";
                }
                //Add anchor part
                $url .= "#vic-widget-" . $attachedWidget->getId() . "-container-anchor";
                break;
            case 'none':
                $url = '#';
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
    public function victoireLink($parameters, $label, $attr = array(), $currentClass = 'active', $avoidUrl = "#")
    {
        extract($parameters);

        if ($linkType == 'attachedWidget') {
            //create base url

            $url = '#';
            if ($attachedWidget && method_exists($attachedWidget->getView(), 'getUrl')) {
                $url = $this->router->generate('victoire_core_page_show', array('url' => $attachedWidget->getView()->getUrl() ));
            }

            if (rtrim($this->request->getRequestUri(), '/') == rtrim($url, '/')) {
                $attr["data-scroll"] = "smooth" ;
            }
        }

        //Avoid to refresh page if not needed
        if ($this->request->getRequestUri() == $this->victoireLinkUrl($parameters, false)) {
            if (!isset($attr['class'])) {
                $attr['class'] = "";
            } else {
                $attr['class'] .= " ";
            }
            $attr['class'] .= $currentClass; //avoid to refresh page when not needed
            $attr["data-scroll"] = "smooth" ;
        }

        //Build the target attribute
        if ($target == "ajax-modal") {
            $attr['data-toggle'] = 'ajax-modal';
        } elseif ($target == "") {
            $attr['target'] = '_parent';
        } else {
            $attr['target'] = $target;
        }
        $attributes = array();
        foreach ($attr as $key => $_attr) {
            if (is_array($_attr)) {
                $attr = implode($_attr, ' ');
            } else {
                $attr = $_attr;
            }
            $attributes[] = $key.'="'.$attr.'"';
        }

        $url = $this->victoireLinkUrl($parameters, true, $avoidUrl);
        //Creates a new twig environment
        $twigEnv = new \Twig_Environment(new \Twig_Loader_String());

        return $twigEnv->render('{{ link|raw }}', array('link' => '<a href="'.$url.'" '.implode($attributes, ' ').'>'.$label.'</a>'));
    }

    /**
     * Generate the complete menu link item (with the li tag)
     * @param array  $parameters The link parameters (go to LinkTrait to have the list)
     * @param string $label      link label
     * @param array  $attr       custom attributes
     *
     * @return string
     */
    public function victoireMenuLink($parameters, $label, $attr = array())
    {
        $linkAttr = array();
        //is the link is active
        if ($this->request->getRequestUri() == $this->victoireLinkUrl($parameters, false)) {
            if (!isset($attr['class'])) {
                $linkAttr['class'] = "";
            }
            $linkAttr['class'] .= "active"; //avoid to refresh page when not needed
        }

        $linkAttributes = array();
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
