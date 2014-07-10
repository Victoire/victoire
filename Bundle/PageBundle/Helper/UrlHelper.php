<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

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
    protected $em = null;

    /**
     * Constructor
     *
     * @param unknown       $router
     * @param EntityManager $entityManager
     */
    public function __construct($router, EntityManager $entityManager)
    {
        $this->router = $router;
        $this->em = $entityManager;
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
        $referer = urldecode($request->server->get('HTTP_REFERER'));

        //remove the base of the url
        $urlReferer = substr($referer, strlen($completeUrl));

        //remove potential parameters
        $position = stripos($urlReferer, "?");
        if ($position > 0) {
            $urlReferer = substr($urlReferer, 0, stripos($urlReferer, "?") );
        }

        return $urlReferer;
    }

    /**
     * Is this url is already used
     *
     * @param string  $url     The url to test
     * @param integer $suffixe The suffixe
     *
     * @return string The next available url
     */
    public function getNextAvailaibleUrl($url, $suffixe = 1)
    {
        $isUrlAlreadyUsed = $this->isUrlAlreadyUsed($url);

        //if the url is alreay used, we look for another one
        if ($isUrlAlreadyUsed) {
            $urlWithSuffix = $url . '-' . $suffixe;

            $isUrlAlreadyUsed = $this->isUrlAlreadyUsed($urlWithSuffix);

            //the url is still used, we try the next one
            if ($isUrlAlreadyUsed) {
                //get the next available url
                $url = $this->getNextAvailaibleUrl($url, $suffixe + 1);
            } else {
                //this url if free, let us use it
                $url = $urlWithSuffix;
            }
        }

        return $url;
    }

    /**
     * Test is the url is already used
     *
     * @param string $url
     *
     * @return boolean Is the url free
     */
    public function isUrlAlreadyUsed($url)
    {
        $isUrlAlreadyUsed = false;

        $em = $this->em;

        //the base page repository
        $repo = $em->getRepository('VictoirePageBundle:Page');

        //try to get a page with this url
        $page = $repo->findOneByUrl($url);

        //a page use this url
        if ($page !== null) {
            $isUrlAlreadyUsed = true;
        }

        return $isUrlAlreadyUsed;
    }
}
