<?php

namespace Victoire\Bundle\CoreBundle\Helper;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Symfony\Component\HttpFoundation\RequestStack;
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
        $this->xmlFile = $cacheDir . '/victoire/viewsReferences.xml';
        $this->requestStack = $requestStack;
    }

    /**
     * Write given views references in a xml file
     * @param array $views
     *
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

    }
    /**
     * Write given views references in a xml file
     * @param array $views
     *
     * @return void
     */
    public function write($viewsReferences)
    {
        $rootNode = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' ?><viewReferences></viewReferences>");

        foreach ($viewsReferences as $viewReference) {
            $itemNode = $rootNode->addChild('viewReference');
            $this->buildItemNode($viewReference, $itemNode);
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
     * @param $locale
     *
     * @return void
     */
    public function update(View $view, $entity = null)
    {

        $rootNode = $this->readCache();
        $id = $this->getViewCacheId($view, $entity);
        $oldItemNode = $rootNode->xpath("//viewReference[@id='" . $id . "']");
        unset($oldItemNode[0][0]);

        $viewReferences = $this->container->get('victoire_core.view_helper')->buildViewReference($view, $entity);

        foreach ($viewReferences as $key => $viewReference) {
            $oldItemNode = $rootNode->xpath("//viewReference[@url='" . $key . "']");
            unset($oldItemNode[0][0]);
            $itemNode = $rootNode->addChild('viewReference');
            $this->buildItemNode($viewReference, $itemNode);
        }
        $this->writeFile($rootNode);
    }

    public function getReferenceByParameters($parameters)
    {
        $locale = array(
            'locale' => '@locale="' . $this->requestStack->getCurrentRequest()->getLocale() . '"'
        );
        $viewReference = array();
        
        foreach ($parameters as $key => $value) {
            $arguments[$key] = '@' . $key . '="' . $value . '"';
        }
        $arguments = array_merge($arguments, $locale);
        
        if ($xmlReference = $this->readCache()->xpath("//viewReference[" . implode(' and ', $arguments) . "]")) {
            $viewReference['id']              = $xmlReference[0]->getAttributeAsPhp('id');
            $viewReference['locale']          = $xmlReference[0]->getAttributeAsPhp('locale');
            $viewReference['entityId']        = $xmlReference[0]->getAttributeAsPhp('entityId');
            $viewReference['entityNamespace'] = $xmlReference[0]->getAttributeAsPhp('entityNamespace');
            $viewReference['url']             = $xmlReference[0]->getAttributeAsPhp('url');
            $viewReference['viewId']          = $xmlReference[0]->getAttributeAsPhp('viewId');
            $viewReference['viewNamespace']   = $xmlReference[0]->getAttributeAsPhp('viewNamespace');
            $viewReference['patternId']       = $xmlReference[0]->getAttributeAsPhp('patternId');
        } else {
            $viewReference = null;
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

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
