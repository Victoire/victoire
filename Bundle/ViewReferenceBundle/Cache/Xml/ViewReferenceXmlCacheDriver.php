<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Xml;

class ViewReferenceXmlCacheDriver
{
    protected $xmlFile;
    public static $baseRootNode = <<<'XML'
<?xml version='1.0' encoding='UTF-8' ?>
<viewReferences/>
XML;

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
        if ($this->fileExists()) {
            $xmlElement = new \SimpleXMLElement(file_get_contents($this->xmlFile));
        } else {
            $xmlElement = new \SimpleXMLElement(self::$baseRootNode);
        }

        return $xmlElement;
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
