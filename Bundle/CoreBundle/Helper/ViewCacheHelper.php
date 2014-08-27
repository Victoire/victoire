<?php

namespace Victoire\Bundle\CoreBundle\Helper;

use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * View cache helper
 * ref: victoire_core.view_cache_helper
 */
class ViewCacheHelper
{
    private $xmlFile;
    private $viewNamePattern = 'ref_{{view.id}}{{entity ? "_" ~ entity.id}}';

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->xmlFile = $cacheDir . '/victoire/viewsReferences.xml';
    }

    /**
     * Write given views references in a xml file
     * @param array $views
     *
     * @return void
     */
    public function write($views)
    {
        $rootNode = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' ?><viewReferences></viewReferences>");
        foreach ($views as $key => $view) {
            $itemNode = $rootNode->addChild('viewReference');
            $itemNode->addAttribute('id', $view['id']);
            $itemNode->addAttribute('url', $view['url']);
            $itemNode->addAttribute('viewId', $view['viewId']);
            $itemNode->addAttribute('viewNamespace', $view['viewNamespace']);
            if (!empty($view['entityId'])) {
                $itemNode->addAttribute('entityId', $view['entityId']);
                $itemNode->addAttribute('entityNamespace', $view['entityNamespace']);
            }
        }

        $this->writeFile($rootNode);
    }

    /**
     * get the content of the view cache file
     *
     * @return SimpleXMLElement
     */
    public function readCache()
    {
        return new SimpleXMLElement(file_get_contents($this->xmlFile));
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
        $rootNode = $this->readCache();
        $id = $this->getViewCacheId($view, $entity);

        $itemNode = $rootNode->xpath("//viewReference[@id='" . $id . "']");

        if (array_key_exists(0, $itemNode)) {
            $itemNode[0]->attributes()->url = $view->getUrl();
            $itemNode[0]->attributes()->view = $view->getId();
            if ($entity) {
                $itemNode[0]->attributes()->entity = $entity->getId();
                $itemNode[0]->attributes()->entity = get_class($entity);
            }
        } else {
            $itemNode = $rootNode->addChild('viewReference');
            $itemNode->addAttribute('id', $id);
            $itemNode->addAttribute('url', $view->getUrl());
            $itemNode->addAttribute('viewNamespace', get_class($view));
            $itemNode->addAttribute('viewId', $view->getId());
            if ($entity) {
                $itemNode->addAttribute('entityId', $entity->getId());
                $itemNode->addAttribute('entityNamespace', get_class($entity));
            }
        }

        $this->writeFile($rootNode);
    }

    public function getReference($url)
    {
        return $this->getReferenceByParameters(array('url' => $url));
    }

    public function getReferenceByParameters($parameters)
    {
        $arguments = array();
        $viewReference = array();
        foreach ($parameters as $key => $value) {
            if ($value !== null) {
                $arguments[] = '@' . $key . '="' . $value . '"';
            }
        }

        if ($xmlReference = $this->readCache()->xpath("//viewReference[" . implode(' and ', $arguments) . "]")) {
            $viewReference['id']              = $xmlReference[0]->getAttributeAsPhp('id');
            $viewReference['entityId']        = $xmlReference[0]->getAttributeAsPhp('entityId');
            $viewReference['entityNamespace'] = $xmlReference[0]->getAttributeAsPhp('entityNamespace');
            $viewReference['url']             = $xmlReference[0]->getAttributeAsPhp('url');
            $viewReference['viewId']          = $xmlReference[0]->getAttributeAsPhp('viewId');
            $viewReference['viewNamespace']   = $xmlReference[0]->getAttributeAsPhp('viewNamespace');
        }

        return $viewReference;

    }

    public function getViewCacheId(View $view, $entity = null)
    {
        $twigEnv = new \Twig_Environment(new \Twig_Loader_String());

        $id = $twigEnv->render($this->viewNamePattern, array(
            'view'   => $view,
            'entity' => $entity,
        ));

        return $id;
    }
    /**
     * write SimpleXMLElement in the cache file
     * @param SimpleXMLElement $rootNode
     *
     * @return void
     */
    protected function writeFile(SimpleXMLElement $rootNode)
    {
        if (! is_dir(dirname($this->xmlFile))) {
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

}
