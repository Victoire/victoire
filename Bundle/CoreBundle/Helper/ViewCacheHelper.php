<?php

namespace Victoire\Bundle\CoreBundle\Helper;

use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * View cache helper
 * ref: victoire_core.view_cache_helper
 */
class ViewCacheHelper
{
    private $xmlFile;
    private $viewNamePattern = 'ref_{{view.id}}{{entity ? "_" ~ entity.id}}';
    private $container;
    private $requestStack;

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir, RequestStack $requestStack)
    {
        $this->xmlFile = $cacheDir.'/victoire/viewsReferences.xml';
        $this->requestStack = $requestStack;
    }

    /**
     * Write given views references in a xml file
     *
     * @param  \SimpleXMLElement $itemNode
     * @return void
     */
    public function buildItemNode($viewReference, $itemNode)
    {
        if (array_key_exists('id', $viewReference)) {
            $itemNode->addAttribute('id', $viewReference['id']);
        }
        if (array_key_exists('url', $viewReference)) {
            $itemNode->addAttribute('url', $viewReference['url']);
        }
        if (array_key_exists('viewId', $viewReference)) {
            $itemNode->addAttribute('viewId', $viewReference['viewId']);
        }
        if (array_key_exists('patternId', $viewReference)) {
            $itemNode->addAttribute('patternId', $viewReference['patternId']);
        }
        if (array_key_exists('viewNamespace', $viewReference)) {
            $itemNode->addAttribute('viewNamespace', $viewReference['viewNamespace']);
        }
        if (array_key_exists('entityId', $viewReference)) {
            $itemNode->addAttribute('entityId', $viewReference['entityId']);
        }
        if (array_key_exists('entityNamespace', $viewReference)) {
            $itemNode->addAttribute('entityNamespace', $viewReference['entityNamespace']);
        }
        if (array_key_exists('locale', $viewReference)) {
            $itemNode->addAttribute('locale', $viewReference['locale']);
        }
        if (array_key_exists('name', $viewReference)) {
            $itemNode->addAttribute('name', $viewReference['name']);
        }
    }
    /**
     * Write given views references in a xml file
     *
     * @return void
     */
    public function write($viewsReferences)
    {
        $rootNode = new \SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' ?><viewReferences></viewReferences>");

        foreach ($viewsReferences as $viewReference) {
            $itemNode = $rootNode->addChild('viewReference');
            $this->buildItemNode($viewReference, $itemNode);
        }

        $this->writeFile($rootNode);
    }

    /**
     * get the content of the view cache file
     *
     * @return \SimpleXMLElement
     */
    public function readCache()
    {
        return new \SimpleXMLElement(file_get_contents($this->xmlFile));
    }

    /**
     * @return array
     */
    public function convertXmlCacheToArray()
    {
        $xml = $this->readCache();

        $cachedArray = json_decode(json_encode((array) $xml), TRUE);
        $viewsReferences = [];

        foreach ($cachedArray['viewReference'] as $cachedViewReference) {
            $viewReference['id']              = !empty($cachedViewReference['@attributes']['id']) ? $cachedViewReference['@attributes']['id'] : null ;
            $viewReference['locale']          = !empty($cachedViewReference['@attributes']['locale']) ? $cachedViewReference['@attributes']['locale'] : null ;
            $viewReference['entityId']        = !empty($cachedViewReference['@attributes']['entityId']) ? $cachedViewReference['@attributes']['entityId'] : null ;
            $viewReference['entityNamespace'] = !empty($cachedViewReference['@attributes']['entityNamespace']) ? $cachedViewReference['@attributes']['entityNamespace'] : null ;
            $viewReference['url']             = !empty($cachedViewReference['@attributes']['url']) ? $cachedViewReference['@attributes']['url'] : null ;
            $viewReference['viewId']          = !empty($cachedViewReference['@attributes']['viewId']) ? $cachedViewReference['@attributes']['viewId'] : null ;
            $viewReference['viewNamespace']   = !empty($cachedViewReference['@attributes']['viewNamespace']) ? $cachedViewReference['@attributes']['viewNamespace'] : null ;
            $viewReference['patternId']       = !empty($cachedViewReference['@attributes']['patternId']) ? $cachedViewReference['@attributes']['patternId'] : null ;
            $viewReference['name']            = !empty($cachedViewReference['@attributes']['name']) ? $cachedViewReference['@attributes']['name'] : null ;

            $viewsReferences[] = $viewReference;
        }

        return $viewsReferences;
    }

    /**
     * update or insert values of given view cache
     * @param View                $view
     * @param BusinessEntity|null $entity
     * @param $locale
     *
     * @return void
     */
    public function update(View $view, $entity = null)
    {
        $rootNode = $this->readCache();
        $id = $this->getViewReferenceId($view, $entity);
        $oldItemNode = $rootNode->xpath("//viewReference[@id='".$id."']");
        unset($oldItemNode[0][0]);
        if (method_exists($view, 'getUrl')) {
            $oldItemNode = $rootNode->xpath("//viewReference[@url='".$view->getUrl()."']");
            unset($oldItemNode[0][0]);
        }

        $viewReferences = $this->container->get('victoire_core.view_helper')->buildViewReference($view, $entity);

        foreach ($viewReferences as $key => $viewReference) {
            $oldItemNode = $rootNode->xpath("//viewReference[@url='".$key."']");
            unset($oldItemNode[0][0]);
            $itemNode = $rootNode->addChild('viewReference');
            $this->buildItemNode($viewReference, $itemNode);
        }
        $this->writeFile($rootNode);
    }

    public function getReferenceByParameters($parameters)
    {
        $locale = array(
            'locale' => '@locale="'.$this->requestStack->getCurrentRequest()->getLocale().'"',
        );
        $viewReference = array();

        foreach ($parameters as $key => $value) {
            $arguments[$key] = '@'.$key.'="'.$value.'"';
        }
        $arguments = array_merge($arguments, $locale);

        if ($xmlReference = $this->readCache()->xpath("//viewReference[".implode(' and ', $arguments)."]")) {
            $viewReference['id']              = XmlUtils::phpize($xmlReference[0]['id']);
            $viewReference['locale']          = XmlUtils::phpize($xmlReference[0]['locale']);
            $viewReference['entityId']        = XmlUtils::phpize($xmlReference[0]['entityId']);
            $viewReference['entityNamespace'] = XmlUtils::phpize($xmlReference[0]['entityNamespace']);
            $viewReference['url']             = XmlUtils::phpize($xmlReference[0]['url']);
            $viewReference['viewId']          = XmlUtils::phpize($xmlReference[0]['viewId']);
            $viewReference['viewNamespace']   = XmlUtils::phpize($xmlReference[0]['viewNamespace']);
            $viewReference['patternId']       = XmlUtils::phpize($xmlReference[0]['patternId']);
            $viewReference['name']            = XmlUtils::phpize($xmlReference[0]['name']);
        } else {
            $viewReference = null;
        }

        return $viewReference;
    }

    public function getViewReferenceId(View $view, $entity = null)
    {
        if ($view instanceof BusinessEntityPage) {
            $entity = $view->getBusinessEntity();
        }
        $twigEnv = new \Twig_Environment(new \Twig_Loader_String());

        $id = $twigEnv->render($this->viewNamePattern, array(
            'view'   => $view,
            'entity' => $entity,
        ));

        return $id;
    }

    /**
     * write \SimpleXMLElement in the cache file
     * @param \SimpleXMLElement $rootNode
     *
     * @return void
     */
    protected function writeFile(\SimpleXMLElement $rootNode)
    {
        if (!is_dir(dirname($this->xmlFile))) {
            mkdir(dirname($this->xmlFile), 0777, true);
        }

        //Used to format result and have a proper indentation
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($rootNode->asXML());
        $dom->saveXML();

        $dom->save($this->xmlFile);
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Does the cache file exists ?
     *
     * @return boolean
     **/
    public function fileExists()
    {
        return file_exists($this->xmlFile);
    }
}
