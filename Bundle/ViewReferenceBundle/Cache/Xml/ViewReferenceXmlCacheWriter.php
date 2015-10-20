<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Xml;

use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Entity\Traits\WebViewTrait;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Transformer\ArrayToViewReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class ViewReferenceXmlCacheWriter extends ViewReferenceXmlCacheReader
{
    /**
     * Write given views references in a xml file.
     * @param array $nodes
     * @param null $rootNode
     * @param bool $writeFile
     *
     * @return \SimpleXMLElement
     */
    public function generateXml(array $nodes, $rootNode = null, $writeFile = true)
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
            foreach ($view->getViewReference() as $key => $value) {
                $itemNode->addAttribute($key, $value);
            }
            if (!empty($node['children'])) {
                $childrenNode = $itemNode->addChild('children');
                $this->generateXml($node['children'], $childrenNode, false);
            }
        }

        return $writeFile ? $this->writeFile($rootNode) : $rootNode;
    }

    /**
     * remove every matching viewsReferences
     * @param $parameters
     *
     * @return int
     **/
    public function removeViewsReferencesByParameters($parameters)
    {
        $rootNode = $this->readCache();
        $counter = 0;
        foreach ($rootNode->xpath(ViewReferenceHelper::buildXpath($parameters)) as $item) {
            unset($item[0]);
            $counter++;
        }

        $this->writeFile($rootNode);

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
     * write \SimpleXMLElement in the cache file.
     *
     * @param \SimpleXMLElement $rootNode
     *
     * @return int
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
}