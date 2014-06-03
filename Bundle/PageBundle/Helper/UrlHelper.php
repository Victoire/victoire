<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: victoire_page.url_helper
 */
class UrlHelper
{
    protected $request = null;
    protected $router = null;

    /**
     * Constructor
     *
     * @param unknown $router
     */
    public function __construct($router)
    {
        $this->router = $router;
    }

    /**
     * Set the current request
     *
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Get the urlMatcher for the template generator
     * It removes what is after the last /
     * and add /{id} to the url
     *
     * @param string $url
     *
     * @return string The url
     */
    public function getGeneralUrlMatcher($url)
    {
        $urlMatcher = null;

        // split on the / character
        $keywords = preg_split("/\//", $url);

        //if there are some words, we pop the last
        if (count($keywords) > 0) {
            array_pop($keywords);
        }

        //add the id to the end of the url
        array_push($keywords, '{id}');

        //rebuild the url
        $urlMatcher = implode('/', $keywords);

        return $urlMatcher;
    }

    /**
     * Get the entity id from the url
     *
     * @param string $url
     * @return string The id
     */
    public function getEntityIdFromUrl($url)
    {
        $entityId = null;

        // split on the / character
        $keywords = preg_split("/\//", $url);

        //if there are some words, we pop the last
        if (count($keywords) > 0) {
            $entityId = array_pop($keywords);
        }

        return $entityId;
    }

    /**
     * Get the url called in the page from the referer of an ajax call
     *
     * @return string
     */
    public function getAjaxUrlRefererWithoutBase()
    {
        $request = $this->request;

        //get the base url
        $router = $this->router;
        $context = $router->getContext();
        //the host
        $host = $context->getHost();
        //the scheme
        $scheme = $context->getScheme();

        //get the complete url
        $completeUrl = $scheme.'://'.$host.'/';

        //the referer
        $referer = $request->server->get('HTTP_REFERER');

        //remove the base of the url
        $urlReferer = substr($referer, strlen($completeUrl));

        return $urlReferer;
    }
}
