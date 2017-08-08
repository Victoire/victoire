<?php

namespace Victoire\Bundle\FilterBundle\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\FilterBundle\Domain\FilterFormFieldQueryHandler;

/**
 * Class BaseFilter.
 */
abstract class BaseFilter extends AbstractType implements BaseFilterInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var FilterFormFieldQueryHandler
     */
    protected $filterQueryHandler;

    /**
     * BaseFilter constructor.
     *
     * @param EntityManager               $em
     * @param RequestStack                $request
     * @param FilterFormFieldQueryHandler $filterQueryHandler
     */
    public function __construct(
        EntityManager $em,
        RequestStack $request,
        FilterFormFieldQueryHandler $filterQueryHandler
    ) {
        $this->entityManager = $em;
        $this->requestStack = $requestStack;
        $this->filterQueryHandler = $filterQueryHandler;
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $parameters
     *
     * @return mixed
     */
    abstract public function buildQuery(QueryBuilder $qb, array $parameters);

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'widget'          => null,
            'multiple'        => false,
            'filter'          => null,
            'listing_id'      => null,
        ]);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request->getCurrentRequest();
    }
}
