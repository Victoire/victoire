<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Xml;

use Gedmo\Uploadable\Mapping\Driver\Xml;
use Victoire\Bundle\ViewReferenceBundle\Builder\Chain\ViewReferenceTransformerChain;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Transformer\XmlToBusinessPageReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\Transformer\XmlToViewReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * ref: victoire_view_reference.cache.repository
 */
class ViewReferenceXmlCacheRepository
{
    protected $driver;
    protected $viewReferenceTransformerChain;

    /**
     * @param ViewReferenceXmlCacheDriver $driver
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
                '/children/viewReference[@slug="%s"]',
                implode('"]/children/viewReference[@slug="', $urlParts)
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
     * Return the first viewReference according to the parameters
     * @param $parameters
     * @return array|null
     */
    public function getOneReferenceByParameters($parameters, $transform = true)
    {
        $viewReferences = $this->getReferencesByParameters($parameters, $transform);
        if (count($viewReferences)) {
            return $viewReferences[0];
        }
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    public function getReferencesByParameters($parameters, $transform = true)
    {
        $viewsReferences = [];
        $arguments = [];

        if ($xmlReferences = $this->driver->readCache()->xpath(ViewReferenceHelper::buildXpath($parameters))) {
            foreach ($xmlReferences as $xmlReference) {
                if ($transform === true) {
                    if (isset($xmlReference['entityId'])) {
                        $viewRefTransformer = new XMLToBusinessPageReferenceTransformer();
                    } else {
                        $viewRefTransformer = new XMLToViewReferenceTransformer();
                    }
                    $viewReference = $viewRefTransformer->transform($xmlReference);
                }
                $viewsReferences[] = $viewReference;
            }
        }

        return $viewsReferences;
    }

    /**
     * get the content of the view cache file.
     *
     * @return []
     */
    public function getTree($node = null)
    {
        $node = $node ?: $this->driver->readCache();
        $viewRefTransformer = new XMLToViewReferenceTransformer();
        $viewsReferences = [];
        foreach ($node->children() as $child) {
            $viewReference = $viewRefTransformer->transform($child);
            $viewReference->setChildren($this->getTree($child));
            $viewsReferences[] = [
                'viewReference' => $viewReference,
            ];
        }


        return $viewsReferences;
    }
}
