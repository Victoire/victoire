<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Xml;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Transformer\XMLToViewReferenceTransformer;

/**
 * ref: victoire_view_reference.cache.reader
 */
class ViewReferenceXmlCacheReader
{
    protected $xmlFile;

    /**
     * @param string              $filePath
     */
    public function __construct($filePath)
    {
        $this->xmlFile = $filePath;
    }

    /**
     * get the content of the view cache file.
     *
     * @return \SimpleXMLElement
     */
    public function readCache()
    {
        return new \SimpleXMLElement(file_get_contents($this->xmlFile));
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

        if ($xmlReference = $this->readCache()->xpath($xpath)) {
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

        if ($xmlReferences = $this->readCache()->xpath(ViewReferenceHelper::buildXpath($parameters))) {
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
        $node = $node ?: $this->readCache();
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

    /**
     * Does the cache file exists ?
     *
     * @return bool
     **/
    public function fileExists()
    {
        return file_exists($this->xmlFile);
    }
}
