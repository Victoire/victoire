<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Symfony\Component\DependencyInjection\SimpleXMLElement;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * Page helper
 * ref: victoire_page.page_cache_helper
 */
class PageCacheHelper
{
    private $xmlFile;
    private $pageNamePattern = 'victoire_page_{{page.id}}{{entity ? "_" ~ entity.id}}';

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->xmlFile = $cacheDir . '/victoire/pages.xml';
    }

    /**
     * Write given pages references in a xml file
     * @param array $pages
     *
     * @return void
     */
    public function writeCache($pages)
    {
        $rootNode = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' ?><references></references>");
        foreach ($pages as $name => $page) {
            $itemNode = $rootNode->addChild('reference');
            $itemNode->addAttribute('name', $name);
            $itemNode->addAttribute('url', $page['url']);
            $itemNode->addAttribute('pageId', $page['pageId']);
            if (!empty($page['entityId'])) {
                $itemNode->addAttribute('entityId', $page['entityId']);
                $itemNode->addAttribute('entityNamespace', $page['entityNamespace']);
            }
        }

        $this->writeFile($rootNode);
    }

    /**
     * get the content of the page cache file
     *
     * @return SimpleXMLElement
     */
    public function readCache()
    {
        return new SimpleXMLElement(file_get_contents($this->xmlFile));
    }
    /**
     * update or insert values of given page cache
     * @param BasePage            $page
     * @param BusinessEntity|null $entity
     * @param boolean             $update
     *
     * @return void
     */
    public function updatePageCache(BasePage $page, $entity = null)
    {
        $rootNode = $this->readCache();
        $name = $this->getPageCacheName($page, $entity);

        $itemNode = $rootNode->xpath("//reference[@name='" . $name . "']");

        if (array_key_exists(0, $itemNode)) {
            $itemNode[0]->attributes()->url = $page->getUrl();
            $itemNode[0]->attributes()->view = $page->getId();
            if ($entity) {
                $itemNode[0]->attributes()->entity = $entity->getId();
                $itemNode[0]->attributes()->entity = get_class($entity);
            }
        } else {
            $itemNode = $rootNode->addChild('reference');
            $itemNode->addAttribute('name', $name);
            $itemNode->addAttribute('url', $page->getUrl());
            $itemNode->addAttribute('pageId', $page->getId());
            if ($entity) {
                $itemNode->addAttribute('entityId', $entity->getId());
                $itemNode->addAttribute('entityNamespace', get_class($entity));
            }
        }

        $this->writeFile($rootNode);
    }
    public function getPageParameters($url)
    {
        $pageParameters = array();
        $pageCache = $this->readCache()->xpath("//reference[@url='" . $url . "']");
        if ($pageCache) {
            $pageParameters['pageId'] = $pageCache[0]->getAttributeAsPhp('pageId');
            $pageParameters['entityId'] = $pageCache[0]->getAttributeAsPhp('entityId');
            $pageParameters['entityNamespace'] = $pageCache[0]->getAttributeAsPhp('entityNamespace');
            $pageParameters['url'] = $pageCache[0]->getAttributeAsPhp('url');
        }

        return $pageParameters;
    }

    public function findPageCacheByParameters($parameters)
    {
        $arguments = array();
        $pageParameters = array();
        foreach ($parameters as $key => $value) {
            if ($value !== null) {
                $arguments[] = '@' . $key . '="' . $value . '"';
            }
        }

        $pageCache = $this->readCache()->xpath("//reference[" . implode(' and ', $arguments) . "]");
        if ($pageCache) {
            $pageParameters['pageId'] = $pageCache[0]->getAttributeAsPhp('pageId');
            $pageParameters['entityId'] = $pageCache[0]->getAttributeAsPhp('entityId');
            $pageParameters['entityNamespace'] = $pageCache[0]->getAttributeAsPhp('entityNamespace');
            $pageParameters['url'] = $pageCache[0]->getAttributeAsPhp('url');
        }

        return $pageParameters;

    }

    protected function getPageCacheName(BasePage $page, $entity)
    {
        $twigEnv = new \Twig_Environment(new \Twig_Loader_String());

        $name = $twigEnv->render($this->pageNamePattern, array(
            'page' => $page,
            'entity' => $entity,
        ));

        return $name;
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
        file_put_contents($this->xmlFile, $rootNode->asXml());
    }

}
