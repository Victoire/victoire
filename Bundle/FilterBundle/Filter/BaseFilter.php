<?php

namespace Victoire\Bundle\FilterBundle\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseFilter extends AbstractType implements BaseFilterInterface
{
    protected $em;
    protected $request;

    /**
     * @param EntityManager $em
     * @param Request       $request
     */
    public function __construct(EntityManager $em, Request $request)
    {
        $this->em = $em;
        $this->request = $request;
    }

    public function buildQuery(QueryBuilder $qb, array $parameters)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

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

    public function getName()
    {
    }
}
