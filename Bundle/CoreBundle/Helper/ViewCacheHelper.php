<?php

namespace Victoire\Bundle\CoreBundle\Helper;

use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * View cache helper
 * ref: victoire_core.view_cache_helper
 */
class ViewCacheHelper
{
    private $xmlFile;
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
     * @return void
     */
    public function write($viewsReferences)
    {
        $xml = <<<'XML'
<?xml version='1.0' encoding='UTF-8' ?>
<viewReferences/>
XML;
        $rootNode = new \SimpleXMLElement($xml);
        foreach ($viewsReferences as $viewReference) {
            $itemNode = $rootNode->addChild('viewReference');
            foreach ($viewReference as $key => $value) {
                $itemNode->addAttribute($key, $value);
            }
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
     *
     * @return void
     */
    public function update(View $view, $entity = null)
    {

        /** @var ViewHelper $viewHelper */
        $viewHelper = $this->container->get('victoire_core.view_helper');
        $businessEntityPageHelper = $this->container->get('victoire_business_entity_page.business_entity_page_helper');
        $rootNode = $this->readCache();

        //Update pattern and its entities
        if(!$entity) {
            $viewReferences = $viewHelper->buildViewReference($view);
            foreach ($viewReferences as $key => $_viewReference) {
                self::removeViewReference($rootNode, $_viewReference);
                $itemNode = $rootNode->addChild('viewReference');
                foreach ($_viewReference as $key => $value) {
                    $itemNode->addAttribute($key, $value);
                }
            }
        }

        //Update only given entity for a pattern
        else {
            $viewReferenceToRemove['id'] = self::getViewReferenceId($view, $entity);
            if (method_exists($view, 'getUrl') && !($view instanceof BusinessEntityPagePattern)) {
                $viewReferenceToRemove['url'] = $view->getUrl();
            }
            self::removeViewReference($rootNode, $viewReferenceToRemove);
            $viewReferences = $viewHelper->buildViewReference($view, $entity);
            foreach ($viewReferences as $key => $_viewReference) {
                $itemNode = $rootNode->addChild('viewReference');
                foreach ($_viewReference as $key => $value) {
                    $itemNode->addAttribute($key, $value);
                }
            }
        }

        $this->writeFile($rootNode);
    }

    public function getReferenceByParameters($parameters)
    {
        $viewReference = array();
        $arguments = array();

        foreach ($parameters as $key => $value) {
            $arguments[$key] = '@'.$key.'="'.$value.'"';
        }

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

    public function getAllReferenceByParameters($parameters)
    {
        $viewsReferences = array();
        $arguments = array();

        foreach ($parameters as $key => $value) {
            $arguments[$key] = '@'.$key.'="'.$value.'"';
        }

        if ($xmlReferences = $this->readCache()->xpath("//viewReference[".implode(' and ', $arguments)."]")) {
            foreach ($xmlReferences as $xmlReference) {
                $viewsReferences[]  = current($xmlReference->attributes());
            }
        } else {
            $viewsReferences = null;
        }

        return $viewsReferences;
    }

    /**
     * remove all views reference that match with parameters
     *
     * @param $parameters
     * @return void
     **/
    public function removeViewsReferencesByParameters($parameters)
    {
        $rootNode = $this->readCache();

        $viewsReferencesToRemove = $this->getAllReferenceByParameters($parameters);
        foreach ($viewsReferencesToRemove as $viewReferenceToRemove) {
            $this->removeViewReference($rootNode, $viewReferenceToRemove);
        }
        $this->writeFile($rootNode);
    }

    /**
     * @param View $view
     * @param $entity
     * @return string
     */
    public static function getViewReferenceId(View $view, $entity = null)
    {
        if ($view instanceof BusinessEntityPage) {
            $entity = $view->getBusinessEntity();
        }

        $id = sprintf('ref_%s', $view->getId());
        if ($entity) {
            $id .= '_'.$entity->getId();
        }

        return $id;
    }

    /**
     * @param \SimpleXMLElement $rootNode
     * @param array             $viewReference
     */
    private static function removeViewReference(\SimpleXMLElement $rootNode, array $viewReference)
    {
        //Clean by searching by id
        $regex = sprintf("//viewReference[@id='%s']", $viewReference['id']);

        //Clean by searching by url
        if (isset($viewReference['url'])) {
            $regex .= sprintf("| //viewReference[@url='%s']", $viewReference['url']);
        }

        foreach ($rootNode->xpath($regex) as $item) {
            unset($item[0]);
        }
    }

    /**
     * write \SimpleXMLElement in the cache file
     * @param \SimpleXMLElement $rootNode
     *
     * @return integer
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

        return $dom->save($this->xmlFile);
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
