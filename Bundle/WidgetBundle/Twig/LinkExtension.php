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
        );
    }

    public function victoireLinkUrl($parameters)
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
                if ($this->request->getRequestUri() == $url) {
                    $url = "#"; //avoid to refresh page when not needed
                }
                break;
            case 'route':
                $url = $this->router->generate($route, $routeParameters);
                break;
            case 'attachedWidget':
                //fallback when a widget is deleted cascading the relation as null (widget_id = null)
                if (!$attachedWidget) {
                    $url = '#';
                    break;
                }
                //create base url
                $url = $this->router->generate('victoire_core_page_show', array('url' => $attachedWidget->getView()->getUrl() ));

                if ($this->request->getRequestUri() == $url) {
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
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'victoire_link_extention';
    }
}
