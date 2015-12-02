<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Xml;

use Gedmo\Uploadable\Mapping\Driver\Xml;
use Victoire\Bundle\ViewReferenceBundle\Builder\Chain\ViewReferenceTransformerChain;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Transformer\XmlToBusinessPageReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\Transformer\XmlToViewReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * ref: victoire_view_reference.cache.repository.
 */
class ViewReferenceXmlCacheRepository
{
    protected $driver;
    protected $viewReferenceTransformerChain;

    /**
     * @param ViewReferenceXmlCacheDriver   $driver
     * @param ViewReferenceTransformerChain $viewReferenceTransformerChain
     */
    public function __construct(ViewReferenceXmlCacheDriver $driver, ViewReferenceTransformerChain $viewReferenceTransformerChain)
    {
        $this->driver = $driver;
        $this->viewReferenceTransformerChain = $viewReferenceTransformerChain;
    }

    /**
     * @param $url
     * @param $locale
     *
     * @return ViewReference
     */
    public function getReferenceByUrl($url, $locale)
    {

        //Every page is the children of someone and the mother of every page is the homepage (empty slug)
        $xpath = sprintf('//viewReference[@slug="" and @locale="%s"]', $locale);

        $urlParts = explode('/', $url);
        if ($url) {
            //add every hierarchy item in the xpath var
            $xpath .= sprintf(
                '/viewReference[@slug="%s"]',
                implode('"]/viewReference[@slug="', $urlParts)
            );
        }

        if ($xmlReference = $this->driver->readCache()->xpath($xpath)) {
            $attr = $xmlReference[0]->attributes();
            $transformer = $this->viewReferenceTransformerChain->getViewReferenceTransformer(
                (string) $attr['viewNamespace'], 'xml'
            );

            $viewReference = $transformer->transform($xmlReference[0]);
        } else {
            $viewReference = null;
        }

        return $viewReference;
    }

    /**
     * Return the first viewReference according to the parameters.
     *
     * @param $parameters
     *
     * @return ViewReference|\SimpleXMLElement|null
     */
    public function getOneReferenceByParameters($parameters, $transform = true, $keepChildren = false)
    {
        $viewReferences = $this->getReferencesByParameters($parameters, $transform, $keepChildren);
        if (count($viewReferences)) {
            return $viewReferences[0];
        }
    }


    /**
     * @param array $parameters
     *
     * @return ViewReference[]|\SimpleXMLElement[]
     */
    public function getReferencesByParameters($parameters, $transform = true, $keepChildren = false)
    {
        $viewsReferences = [];

        /** @var \SimpleXmlElement[] $xmlReferences */
        if ($xmlReferences = $this->driver->readCache()->xpath(ViewReferenceHelper::buildXpath($parameters))) {
            foreach ($xmlReferences as $reference) {
                if ($transform === true) {
                    $transformViewReferenceFn = function ($parentViewReference) use (&$transformViewReferenceFn, $keepChildren) {
                        $transformer = ViewReferenceXmlCacheRepository::findTransformerFromXmlElement($parentViewReference);
                        $reference = $transformer->transform($parentViewReference);
                        if ($keepChildren) {
                            foreach ($parentViewReference->children() as $child) {
                                $reference->addChild($transformViewReferenceFn($child));
                            }
                        }

                        return $reference;
                    };

                    $reference = $transformViewReferenceFn($reference);

                }
                $viewsReferences[] = $reference;
            }
        }

        return $viewsReferences;
    }

    /**
     * get the content of the view cache file.
     *
     * @param \SimpleXMLElement $node
     * @param int               $depth
     *
     * @return \array[]
     */
    public function getChoices(\SimpleXMLElement $node = null, $depth = 0)
    {
        $viewsReferences = [];

        $prefixFn = function ($depth, $char0 = '└', $char = '─') {
            $prefix = $char0;
            for ($i = 0; $i <= $depth; $i++) {
                $prefix .= $char;
            }

            return $prefix;
        };

        if (null === $node) {
            $node = $this->driver->readCache();
        }

        foreach ($node as $child) {
            $viewReferenceTransformer = self::findTransformerFromXmlElement($child);
            $viewReference = $viewReferenceTransformer->transform($child);
            if ($viewReference->getName() != '') {
                $prefix = '';
                if ($depth > 0) {
                    $prefix = $prefixFn($depth).' ';
                }
                $viewsReferences[$viewReference->getId()] = $prefix.$viewReference->getName();
            }

            $viewsReferences = array_merge($viewsReferences, $this->getChoices($child, $depth + 1));
        }

        return $viewsReferences;
    }

    /**
     * @param \SimpleXMLElement $xmlElement
     */
    public static function findTransformerFromXmlElement($xmlElement)
    {
        if (isset($xmlElement['entityId'])) {
            $viewRefTransformer = new XMLToBusinessPageReferenceTransformer();
        } else {
            $viewRefTransformer = new XMLToViewReferenceTransformer();
        }

        return $viewRefTransformer;
    }
}
