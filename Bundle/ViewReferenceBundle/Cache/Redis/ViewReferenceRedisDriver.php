<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Redis;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\Chain\ViewReferenceTransformerChain;
use Victoire\Bundle\ViewReferenceBundle\Cache\UrlManager;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\Transformer\ArrayToBusinessPageReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\Transformer\ArrayToViewReferenceTransformer;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * Class ViewReferenceRedisDriver.
 */
class ViewReferenceRedisDriver
{
    private $manager;
    private $repository;
    private $transformer;
    private $tools;
    private $urlManager;

    /**
     * ViewReferenceRedisDriver constructor.
     *
     * @param ViewReferenceRedisManager     $manager
     * @param ViewReferenceRedisRepository  $repository
     * @param ViewReferenceTransformerChain $transformer
     * @param UrlManager                    $urlManager
     */
    public function __construct(ViewReferenceRedisManager $manager, ViewReferenceRedisRepository $repository, ViewReferenceTransformerChain $transformer, UrlManager $urlManager)
    {
        $this->manager = $manager;
        $this->transformer = $transformer;
        $this->repository = $repository;
        $this->tools = new ViewReferenceRedisTool();
        $this->urlManager = $urlManager;
    }

    /**
     * This method return a ViewReference for a View.
     *
     * @param View $view
     *
     * @throws \Exception
     *
     * @return ViewReference
     */
    public function findReferenceByView(View $view)
    {
        $referenceId = ViewReferenceHelper::generateViewReferenceId($view);
        $reference = $this->getOneReferenceByParameters($referenceId, false);

        return $reference;
    }

    /**
     * This method save a tree of viewReferences.
     *
     * @param array     $viewReferences
     * @param null      $parentId
     * @param bool|true $reset
     */
    public function saveReferences(array $viewReferences, $parentId = null, $reset = true)
    {
        // Reset redis if wanted
        if ($reset) {
            $this->manager->reset();
        }
        // Parse the viewReferences
        foreach ($viewReferences as $viewReference) {
            $reference = $viewReference['view']->getReference();
            // save the viewReference
            $id = $this->saveReference($reference, $parentId);
            // if children save them
            if (array_key_exists('children', $viewReference) && !empty($children = $viewReference['children'])) {
                $this->saveReferences($children, $id, false, '');
            }
        }
    }

    /**
     * This method save a Reference.
     *
     * @param ViewReference $viewReference
     * @param null          $parentId
     *
     * @return mixed
     */
    public function saveReference(ViewReference $viewReference, $parentId = null)
    {
        // Transform the viewReference in array
        $arrayTransformer = $this->transformer->getViewReferenceTransformer(
            $viewReference->getViewNamespace(), 'array'
        );
        $referenceArray = $arrayTransformer->reverseTransform($viewReference);
        // Remove old url if exist
        $this->urlManager->removeUrlForViewReference($viewReference);
        // Update/create the viewReference
        $this->manager->update($referenceArray['id'], $referenceArray);
        // Build the url for reference
        $this->urlManager->buildUrl($viewReference);
        // Set parent if exist
        if ($parentId) {
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
        $this->urlManager->removeUrl($url, $viewReference->getLocale());
        // Remove reference
        $this->manager->remove($referenceId);
    }

    /**
     * This method return a ViewReference fo an url/locale.
     *
     * @param $url
     * @param $locale
     *
     * @return mixed|null
     */
    public function getReferenceByUrl($url, $locale)
    {
        // Find the ref id for an url/locale
        $refId = $this->urlManager->findRefIdByUrl($url, $locale);
        if (!$refId) {
            return;
        }
        // Get the reference
        $ref = $this->tools->unredislizeArray($this->repository->findById($refId));
        // Transform the reference into a viewReference
        $transformer = $this->transformer->getViewReferenceTransformer(
            (string) $ref['viewNamespace'], 'array'
        );

        $viewReference = $transformer->transform($ref);

        return $viewReference;
    }

    /**
     * Get references matching with parameters.
     *
     * @param $parameters
     * @param bool|true  $transform
     * @param bool|false $keepChildren
     *
     * @return array
     */
    public function getReferencesByParameters($parameters, $transform = true, $keepChildren = false)
    {
        $viewsReferences = [];
        $refsId = $this->repository->getAllBy($parameters);

        $references = $this->repository->getResults($refsId);
        foreach ($references as $reference) {
            $reference = $this->tools->unredislizeArray($reference);
            if ($transform === true) {
                $transformViewReferenceFn = function ($parentViewReference) use (&$transformViewReferenceFn, $keepChildren) {
                    $transformer = ViewReferenceRedisDriver::findTransformerFromElement($parentViewReference);
                    $reference = $transformer->transform($parentViewReference);
                    if ($keepChildren) {
                        foreach ($this->repository->getChildren($parentViewReference->getId()) as $child) {
                            $reference->addChild($transformViewReferenceFn($this->repository->findById($child)));
                        }
                    }

                    return $reference;
                };

                $reference = $transformViewReferenceFn($reference);
            }
            $viewsReferences[] = $reference;
        }

        return $viewsReferences;
    }

    /**
     * Get first reference matching with parameters.
     *
     * @param $parameters
     * @param bool|true  $transform
     * @param bool|false $keepChildren
     *
     * @return mixed
     */
    public function getOneReferenceByParameters($parameters, $transform = true, $keepChildren = false)
    {
        $result = $this->getReferencesByParameters($parameters, $transform, $keepChildren);
        if (count($result)) {
            return $result[0];
        }
    }

    /**
     * Check if redis has a reference.
     *
     * @return bool
     */
    public function hasReference()
    {
        if (count($this->repository->getAll())) {
            return true;
        }

        return false;
    }

    /**
     * get ViewsReferences ordered byhierarchy with some prefix.
     *
     * @param null $refId
     * @param int  $depth
     *
     * @return array
     */
    public function getChoices($refId = null, $depth = 0)
    {
        $viewsReferences = [];

        $prefixFn = function ($depth, $char0 = '└', $char = '─') {
            $prefix = $char0;
            for ($i = 0; $i <= $depth; $i++) {
                $prefix .= $char;
            }

            return $prefix;
        };

        if (null === $refId) {
            $refsId = $this->repository->getAllBy([
                'slug' => '',
            ]);
        } else {
            $refsId = $this->repository->getAllBy([
                'parent' => $refId,
            ]);
        }
        $refs = $this->repository->getResults($refsId);

        foreach ($refs as $ref) {
            $reference = $this->tools->unredislizeArray($ref);
            $viewReferenceTransformer = self::findTransformerFromElement($reference);
            $viewReference = $viewReferenceTransformer->transform($reference);
            if ($viewReference->getName() != '') {
                $prefix = '';
                if ($depth > 0) {
                    $prefix = $prefixFn($depth).' ';
                }
                $viewsReferences[$viewReference->getId()] = $prefix.$viewReference->getName();
            }

            $viewsReferences = array_merge($viewsReferences, $this->getChoices($viewReference->getId(), $depth + 1));
        }

        return $viewsReferences;
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
}
