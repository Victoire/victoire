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
    private $choices;

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
        $reference = $this->getOneReferenceByParameters(['id' => $referenceId], false);
        $transformer = $this->transformer->getViewReferenceTransformer(
            (string) $reference['viewNamespace'], 'array'
        );

        return  $transformer->transform($reference);
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

        return $transformer->transform($ref);
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
    public function getReferencesByParameters(array $parameters, $transform = true, $keepChildren = false, $type = null)
    {
        $viewsReferences = [];
        $refsId = $this->repository->getAllBy($parameters, $type);

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
    public function getOneReferenceByParameters(array $parameters, $transform = true, $keepChildren = false)
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
     * get ViewsReferences ordered by hierarchy with a breadcrumb.
     *
     * @param string $locale      the locale to get views as choices
     * @param string $parentRefId the parent to get views as choices
     * @param int    $depth       how deep we go recursively to get choices tree
     * @param string $parentName  name of parent page
     *
     * @return array
     */
    public function getChoices($locale, $parentRefId = null, $depth = 0, $parentName = '')
    {
        $viewsReferences = [];

        if (null !== $parentRefId) {
            $refsId = $this->repository->getAllBy([
                'locale' => $locale,
                'parent' => $parentRefId,
            ]);
        } else {
            $refsId = $this->repository->getAllBy([
                'locale' => $locale,
                'slug'   => '',
            ]);
        }

        $refs = $this->repository->getResults($refsId);

        foreach ($refs as $reference) {
            $viewReferenceTransformer = ViewReferenceManager::findTransformerFromElement($reference);
            $viewReference = $viewReferenceTransformer->transform($reference);
            $name = null;

            if ($viewReference->getName() != '') {
                $name = $viewReference->getName();

                if ('' !== $parentName) {
                    $name = $parentName.' › '.$name;
                }

                $viewsReferences[] = [
                    'text' => $name,
                    'id'   => $viewReference->getId(),
                ];
            }

            $viewsReferences = array_merge($viewsReferences, $this->getChoices($locale, $viewReference->getId(), $depth + 1, $name));
        }

        return $viewsReferences;
    }

    /**
     * get choices, cache them if they are not cached yet.
     *
     * @param string $locale the locale to get views as choices
     *
     * @return array
     */
    public function getCachedChoices($locale)
    {
        if (null === $this->choices) {
            $choices = $this->getChoices($locale);

            foreach ($choices as $choice) {
                $this->choices[$choice['id']] = $choice['text'];
            }
        }

        return $this->choices;
    }
}
