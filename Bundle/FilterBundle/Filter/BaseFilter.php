<?php

namespace Victoire\Bundle\FilterBundle\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseFilter extends AbstractType implements BaseFilterInterface
{
    protected $entityManager;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param EntityManager $em
     * @param Request       $request
     */
    public function __construct(EntityManager $em, RequestStack $requestStack)
    {
        $this->entityManager = $em;
        $this->requestStack = $requestStack;
    }

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
        return $this->requestStack->getCurrentRequest();
    }
}
