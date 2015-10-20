<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Xml;

use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Transformer\XMLToViewReferenceTransformer;

/**
 * ref: victoire_view_reference.cache.repository
 */
class ViewReferenceXmlCacheRepository
{
    protected $driver;

    /**
     * @param ViewReferenceXmlCacheDriver $driver
     */
    public function __construct(ViewReferenceXmlCacheDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param $url
     * @param $locale
     * @return null|\SimpleXMLElement
     */
    public function getReferenceByUrl($url, $locale)
    {

        //Every page is the children of someone and the mother of every page is the homepage (empty slug)
        $xpath = sprintf('//viewReference[@slug="" and @locale="%s"]', $locale);

        $urlParts = explode('/', $url);
        if ($url !== "") {
            //add every hierarchy item in the xpath var
            $xpath .= sprintf(
                '/children/viewReference[@slug="%s"]',
                implode('"]/children/viewReference[@slug="', $urlParts)
            );
        }

        if ($xmlReference = $this->driver->readCache()->xpath($xpath)) {
            $viewReference = $xmlReference[0]->attributes();
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
        $viewRefTransformer = new XMLToViewReferenceTransformer();

        if ($xmlReferences = $this->driver->readCache()->xpath(ViewReferenceHelper::buildXpath($parameters))) {
            foreach ($xmlReferences as $xmlReference) {
                $viewReference = current($xmlReference->attributes());
                if ($transform === true) {
                    $viewReference = $viewRefTransformer->reverseTransform($viewReference);
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
