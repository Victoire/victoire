<?php

namespace Victoire\Bundle\CoreBundle\Helper;

use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Builder\ViewReferenceBuilder;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * View cache helper
 * ref: victoire_core.view_cache_helper
 */
class ViewCacheHelper
{
    private $xmlFile;
    private $requestStack;
    private $viewReferenceHelper;

    /**
     * @param string $cacheDir
     * @param RequestStack $requestStack
     * @param ViewReferenceBuilder $viewReferenceBuilder
     * @param ViewReferenceHelper $viewReferenceHelper
     */
    public function __construct($cacheDir, RequestStack $requestStack, ViewReferenceHelper $viewReferenceHelper)
    {
        $this->xmlFile = $cacheDir.'/victoire/viewsReferences.xml';
        $this->requestStack = $requestStack;
        $this->viewReferenceHelper = $viewReferenceHelper;
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
     * update or insert values of given view cache
     * The given view can only be a WebView cause it have to got an url
     * if you want to update a BusinessTemplate and all its BP|VBP, you have to call updateTemplate method
     * @param $viewReferences
     * @internal param View $view
     * @internal param null|BusinessEntity $entity
     *
     * @return \SimpleXMLElement
     */
    public function update($viewReferences)
    {
        $rootNode = $this->readCache();

        foreach ($viewReferences as $key => $_viewReference) {
            $parameters = [
                'patternId' => !empty($_viewReference['patternId']) ? $_viewReference['patternId'] : null,
                'entityId' => !empty($_viewReference['entityId']) ? $_viewReference['entityId'] : null,
                'viewNamespace' => 'Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage',
            ];

            $this->viewReferenceHelper->removeViewReference($rootNode, ['id' => $_viewReference['id']]);
            $viewsReferencesToRemove = $this->getAllReferenceByParameters($parameters);
            foreach ($viewsReferencesToRemove as $viewReferenceToRemove) {
                $this->viewReferenceHelper->removeViewReference($rootNode, $viewReferenceToRemove);
            }
            $itemNode = $rootNode->addChild('viewReference');
            foreach ($_viewReference as $key => $value) {
                // the key 'view' is the view object, we do not want to write it in the cache file
                if ($key !== 'view') {
                    $itemNode->addAttribute($key, $value);
                }
            }
        }

        $allViewsReferences = $this->viewReferenceHelper->convertXmlCacheToArray($rootNode);
        $allViewsReferences = $this->viewReferenceHelper->uniqueUrls($allViewsReferences);

        $this->write($allViewsReferences);

        return $viewReferences;

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

    /**
     * @param array $parameters
     * @return array
     */
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
            $this->viewReferenceHelper->removeViewReference($rootNode, $viewReferenceToRemove);
        }
        $this->writeFile($rootNode);
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


    /**
     * Does the cache file exists ?
     *
     * @return boolean
     **/
    public function fileExists()
    {
        return file_exists($this->xmlFile);
    }

    /**
     * This method get all views (BasePage and Template) in DB and return the references, including non persisted Business entity page (pattern and businessEntityId based)
     * @return array the computed views as array
     */
    public function getAllViewsReferences()
    {

        $xml = $this->readCache();
        $viewsReferences = $this->viewReferenceHelper->convertXmlCacheToArray($xml);

        return $viewsReferences;
    }

}
