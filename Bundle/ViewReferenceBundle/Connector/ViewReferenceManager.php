<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\Chain\ViewReferenceTransformerChain;
use Victoire\Bundle\ViewReferenceBundle\Transformer\ArrayToBusinessPageReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\Transformer\ArrayToViewReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * Class ViewReferenceManager.
 */
class ViewReferenceManager
{
    private $manager;
    private $repository;
    private $transformer;

    /**
     * ViewReferenceManager constructor.
     *
     * @param ViewReferenceConnectorManagerInterface    $manager
     * @param ViewReferenceConnectorRepositoryInterface $repository
     * @param ViewReferenceTransformerChain             $transformer
     */
    public function __construct(ViewReferenceConnectorManagerInterface $manager, ViewReferenceConnectorRepositoryInterface $repository, ViewReferenceTransformerChain $transformer)
    {
        $this->manager = $manager;
        $this->transformer = $transformer;
        $this->repository = $repository;
    }

    /**
     * This method save a tree of viewReferences.
     *
     * @param array  $viewReferences
     * @param string $parentId
     * @param string $parentLocale
     * @param bool   $reset
     */
    public function saveReferences(array $viewReferences, $parentId = null, $parentLocale = null, $reset = true)
    {
        // Reset redis if wanted
        if ($reset) {
            $this->manager->reset();
        }

        // Parse the viewReferences
        foreach ($viewReferences as $viewReference) {
            /** @var View $view */
            $view = $viewReference['view'];
            foreach ($view->getReferences() as $locale => $reference) {
                // save the viewReference
                $id = $this->saveReference($reference, $parentId, $parentLocale);
                // if children, save them
                if (array_key_exists('children', $viewReference) && !empty($children = $viewReference['children'])) {
                    $this->saveReferences($children, $id, $reference->getLocale(), false);
                }
            }
        }
    }

    /**
     * This method save a Reference.
     *
     * @param ViewReference $viewReference
     * @param string        $parentId
     * @param string        $parentLocale
     *
     * @return mixed
     */
    public function saveReference(ViewReference $viewReference, $parentId = null, $parentLocale = null)
    {
        // Transform the viewReference in array
        $arrayTransformer = $this->transformer->getViewReferenceTransformer(
            $viewReference->getViewNamespace(), 'array'
        );
        $referenceArray = $arrayTransformer->reverseTransform($viewReference);
        // Remove old url if exist
        $this->removeUrlForViewReference($viewReference);
        // Update/create the viewReference
        $this->manager->update($referenceArray['id'], $referenceArray);
        // Build the url for reference
        $this->manager->buildUrl($viewReference->getId());
        // Set parent if exist
        if ($parentId && $parentLocale === $viewReference->getLocale()) {
            $this->manager->addChild($parentId, $referenceArray['id']);
        }

        return $referenceArray['id'];
    }

    /**
     * This method remove reference for a ViewReference.
     *
     * @param ViewReference $viewReference
     */
    public function removeReference(ViewReference $viewReference)
    {
        $referenceId = $viewReference->getId();
        $url = $this->repository->findValueForId('url', $referenceId);
        // Remove url
        $this->manager->removeUrl($url, $viewReference->getLocale());
        // Remove reference
        $this->manager->remove($referenceId);
    }

    /**
     * Find the transformer for an element.
     *
     * @param $element
     *
     * @return ArrayToBusinessPageReferenceTransformer|ArrayToViewReferenceTransformer
     */
    public static function findTransformerFromElement($element)
    {
        if (isset($element['entityId'])) {
            $viewRefTransformer = new ArrayToBusinessPageReferenceTransformer();
        } else {
            $viewRefTransformer = new ArrayToViewReferenceTransformer();
        }

        return $viewRefTransformer;
    }

    /**
     * Remove an url for a viewReference with his reference in redis.
     *
     * @param ViewReference $viewReference
     */
    public function removeUrlForViewReference(ViewReference $viewReference)
    {
        $id = $viewReference->getId();
        if ($url = $this->repository->findValueForId('url', $id)) {
            $this->manager->removeUrl($url, $this->repository->findValueForId('locale', $id));
        }
    }
}
