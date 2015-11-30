<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Xml;

use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Entity\Traits\WebViewTrait;
use Victoire\Bundle\ViewReferenceBundle\Builder\Chain\ViewReferenceTransformerChain;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Transformer\ArrayToBusinessPageReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\Transformer\ArrayToViewReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\Transformer\XmlToBusinessPageReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\Transformer\XmlToViewReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\BusinessPageReference;
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
    public function generateXml(array $nodes, $rootNode = null, $url = '')
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
            $viewReference = $view->getViewReference();
            $arrayTransformer = $this->viewReferenceTransformerChain->getViewReferenceTransformer(
                $viewReference->getViewNamespace(), 'array'
            );
            foreach ($arrayTransformer->reverseTransform($viewReference) as $key => $value) {
                $itemNode->addAttribute($key, $value);
            }

            $_url = ltrim($url.'/'.$viewReference->getSlug(), '/');
            $itemNode->attributes()->url = $_url;

            //Build url thanks to hierarchy
            if (!empty($node['children'])) {
                $childrenNode = $itemNode->addChild('children');
                $this->generateXml($node['children'], $childrenNode, $_url);
            }
        }

        return $rootNode;
    }

    #have a look to git history to find "add" and "remove" methods
}