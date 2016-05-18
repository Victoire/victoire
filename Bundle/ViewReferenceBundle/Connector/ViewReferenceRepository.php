<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector;

use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\Chain\ViewReferenceTransformerChain;
use Victoire\Bundle\ViewReferenceBundle\Helper\ViewReferenceHelper;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * Class ViewReferenceRepository.
 */
class ViewReferenceRepository
{
    private $repository;
    private $transformer;

    /**
     * ViewReferenceManager constructor.
     *
     * @param ViewReferenceConnectorRepositoryInterface $repository
     * @param ViewReferenceTransformerChain             $transformer
     */
    public function __construct(ViewReferenceConnectorRepositoryInterface $repository, ViewReferenceTransformerChain $transformer)
    {
        $this->repository = $repository;
        $this->transformer = $transformer;
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
        $reference = $this->repository->findById($referenceId);
        $transformer = $this->transformer->getViewReferenceTransformer(
            (string) $reference['viewNamespace'], 'array'
        );

        $viewReference = $transformer->transform($reference);

        return $viewReference;
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
        $refId = $this->repository->findRefIdByUrl($url, $locale);
        if (!$refId) {
            return;
        }
        // Get the reference
        $ref = $this->repository->findById($refId);
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
            if ($transform === true) {
                $transformViewReferenceFn = function ($parentViewReference) use (&$transformViewReferenceFn, $keepChildren) {
                    $transformer = ViewReferenceManager::findTransformerFromElement($parentViewReference);
                    $reference = $transformer->transform($parentViewReference);
                    if ($keepChildren) {
                        foreach ($this->repository->getChildren($parentViewReference['id']) as $child) {
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
     * get ViewsReferences ordered by hierarchy with a decoractor.
     *
     * @param string $locale the locale to get views as choices
     * @param string $refId  the parent to get views as choices
     * @param int    $depth  how deep we go recursively to get choices tree
     *
     * @return array
     */
    public function getChoices($locale, $refId = null, $depth = 0)
    {
        $viewsReferences = [];

        $decoratorFn = function ($depth, $char0 = '└', $char = '─') {
            $decorator = $char0;
            for ($i = 0; $i <= $depth; $i++) {
                $decorator .= $char;
            }

            return $decorator;
        };

        if (null === $refId) {
            $refsId = $this->repository->getAllBy([
                'locale' => $locale,
                'slug'   => '',
            ]);
        } else {
            $refsId = $this->repository->getAllBy([
                'locale' => $locale,
                'parent' => $refId,
            ]);
        }
        $refs = $this->repository->getResults($refsId);

        foreach ($refs as $reference) {
            $viewReferenceTransformer = ViewReferenceManager::findTransformerFromElement($reference);
            $viewReference = $viewReferenceTransformer->transform($reference);
            if ($viewReference->getName() != '') {
                $decorator = '';
                if ($depth > 0) {
                    $decorator = $decoratorFn($depth).' ';
                }
                $viewsReferences[$decorator.$viewReference->getName()] = $viewReference->getId();
            }

            $viewsReferences = array_merge($viewsReferences, $this->getChoices($locale, $viewReference->getId(), $depth + 1));
        }

        return $viewsReferences;
    }
}
