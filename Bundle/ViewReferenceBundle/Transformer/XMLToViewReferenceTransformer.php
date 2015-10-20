<?php

namespace Victoire\Bundle\ViewReferenceBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

class XMLToViewReferenceTransformer implements DataTransformerInterface
{

    /**
     * SimpleXMLElement to ViewReference
     * @inheritdoc
     * @param \SimpleXMLElement $xml
     *
     * @return ViewReference
     */
    public function transform($xmlElement)
    {
        $cachedArray = json_decode(json_encode((array) $xmlElement), true);
        // if the xml contains only one reference, it'll be flatten so it will miss one deep level, so we re-create it
        if (count($cachedArray['viewReference']) === 1) {
            $cachedArray['viewReference'] = [$cachedArray['viewReference']];
        }

        $viewReference = new ViewReference();
        foreach ($cachedArray['viewReference'] as $cachedViewReference) {
            foreach ($cachedViewReference['@attributes'] as $prop => $value) {
                $methodName = 'set'.ucfirst($prop);
                if (method_exists($viewReference, $methodName)) {
                    $viewReference->$methodName((string) $value);
                }
            }
        }

        return $viewReference;
    }

    /**
     * View Reference to SimpleXMLElement
     * @inheritdoc
     * @param ViewReference $viewReference
     *
     * @return \SimpleXMLElement $xml
     */
    public function reverseTransform($viewReference)
    {
        $xml = <<<'XML'
<?xml version='1.0' encoding='UTF-8' ?>
<viewReference/>
XML;

        $element = new \SimpleXMLElement($xml);

        foreach (ViewReferenceHelper::properties as $prop) {
            $methodName = 'get'.ucfirst($prop);
            if (method_exists($viewReference, $methodName)) {
                $element->addAttribute($prop, $viewReference->$methodName);
            }

        }

        return $element;
    }
}