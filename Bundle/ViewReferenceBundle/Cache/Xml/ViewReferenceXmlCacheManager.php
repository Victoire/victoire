<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Xml;

use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Entity\Traits\WebViewTrait;
use Victoire\Bundle\ViewReferenceBundle\Builder\Chain\ViewReferenceTransformerChain;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Transformer\ArrayToViewReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class ViewReferenceXmlCacheManager
{
    protected $driver;

    /**
     * @param ViewReferenceXmlCacheDriver $driver
     */
    public function __construct(ViewReferenceXmlCacheDriver $driver, ViewReferenceTransformerChain $viewReferenceTransformerChain)
    {
        $this->driver = $driver;
        $this->viewReferenceTransformerChain = $viewReferenceTransformerChain;
    }

    /**
     * Write given views references in a xml file.
     * @param array $nodes
     * @param null $rootNode
     *
     * @return \SimpleXMLElement
     */
    public function generateXml(array $nodes, $rootNode = null)
    {
        if (is_null($rootNode)) {
            $xml = <<<'XML'
<?xml version='1.0' encoding='UTF-8' ?>
<viewReferences/>
XML;
            $rootNode = new \SimpleXMLElement($xml);
        }
        foreach ($nodes as $node) {
            $itemNode = $rootNode->addChild('viewReference');

            /** @var WebViewInterface $view */
            $view = $node['view'];
            $arrayTransformer = $this->viewReferenceTransformerChain->getViewReferenceTransformer(
                $view->getViewReference()->getViewNamespace(), 'array'
            );
            foreach ($arrayTransformer->reverseTransform($view->getViewReference()) as $key => $value) {
                $itemNode->addAttribute($key, $value);
            }
            if (!empty($node['children'])) {
                $childrenNode = $itemNode->addChild('children');
                $this->generateXml($node['children'], $childrenNode);
            }
        }

        return $rootNode;
    }

    /**
     * remove every matching viewsReferences
     * @param $parameters
     *
     * @return int
     **/
    public function removeViewsReferencesByParameters($parameters)
    {
        $rootNode = $this->driver->readCache();
        $counter = 0;
        foreach ($rootNode->xpath(ViewReferenceHelper::buildXpath($parameters)) as $item) {
            unset($item[0]);
            $counter++;
        }

        $this->driver->writeFile($rootNode);

        return $counter;
    }

    /**
     * remove a viewReference
     * @param ViewReference $viewReference
     *
     * @return int the number of viewReference deleted
     **/
    public function removeViewReference(ViewReference $viewReference)
    {
        $transformer = new ArrayToViewReferenceTransformer();

        return $this->removeViewsReferencesByParameters($transformer->reverseTransform($viewReference));
    }

    /**
     * add
     **/
    public function add($viewReference)
    {
        //@todo implements add method
    }

    /**
     * flush
     **/
    public function flush()
    {
        $this->driver->writeFile($this->viewsReferences);
    }
}