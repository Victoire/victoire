<?php

namespace Victoire\Bundle\ViewReferenceBundle\Builder\Chain;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ViewReferenceTransformerChain.
 */
class ViewReferenceTransformerChain
{
    private $viewsReferenceTransformers;

    public function __construct()
    {
        $this->viewsReferenceTransformers = [];
    }

    /**
     * add a view Manager.
     *
     * @param DataTransformerInterface $viewManager
     * @param string                   $viewNamespace
     */
    public function addTransformer(DataTransformerInterface $transformer, $viewNamespace, $outputFormat)
    {
        if (!array_key_exists($viewNamespace, $this->viewsReferenceTransformers)) {
            $this->viewsReferenceTransformers[$viewNamespace] = [];
        }
        if (!array_key_exists($outputFormat, $this->viewsReferenceTransformers[$viewNamespace])) {
            $this->viewsReferenceTransformers[$viewNamespace][$outputFormat] = null;
        }
        $this->viewsReferenceTransformers[$viewNamespace][$outputFormat] = $transformer;
    }

    /**
     * @param string $viewNamespace
     * @param string $outputFormat
     *
     * @return DataTransformerInterface
     */
    public function getViewReferenceTransformer($viewNamespace, $outputFormat = 'xml')
    {
        switch(array_key_exists($viewNamespace, $this->viewsReferenceTransformers)) {
            case true:
                return $this->viewsReferenceTransformers[$viewNamespace][$outputFormat];
            case false:
                return $this->viewsReferenceTransformers['Victoire\Bundle\PageBundle\Entity\BasePage'][$outputFormat];
        }
    }
}
