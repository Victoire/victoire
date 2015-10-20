<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Xml;

use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Entity\Traits\WebViewTrait;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Transformer\ArrayToViewReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class ViewReferenceXmlCacheDriver extends ViewReferenceXmlCacheRepository
{
    protected $xmlFile;

    /**
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->xmlFile = $filePath;
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
     * write \SimpleXMLElement in the cache file.
     *
     * @param \SimpleXMLElement $rootNode
     *
     * @return int
     */
    public function writeFile(\SimpleXMLElement $rootNode)
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