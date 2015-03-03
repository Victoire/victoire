<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Victoire\Bundle\FilterBundle\Filter\BaseFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * CategoryFilter form type
 */
class CategoryFilter extends BaseFilter
{
    protected $em;
    protected $request;

    /**
     *
     * @param EntityManager $em
     *
     * @param Request $request
     */
    public function __construct(EntityManager $em, $request)
    {
        $this->em = $em;
        $this->request = $request;
    }

    /**
     * Build the query
     *
     * @param QueryBuilder &$qb
     * @param array        $parameters
     *
     * @return QueryBuilder
     */
    public function buildQuery(QueryBuilder $qb, array $parameters)
    {
        if (!is_array($parameters['category'])) {
            $parameters['category'] =  array($parameters['category']);
        }
        //clean the parameters from the blank value
        foreach ($parameters['category'] as $index => $parameter) {
            //the blank value is removed
            if ($parameter === '') {
                unset($parameters['category'][$index]);
            }
        }

        if (count($parameters['category']) > 0) {
            $qb = $qb
                ->join('main_item.category', 'c')
                ->andWhere('c.id IN (:category)')
                ->setParameter('category', $parameters['category']);
        }

        return $qb;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = $this->em->getRepository('VictoireBlogBundle:Category')->findAll();

        //the blank value
        $categoriesChoices = array();

        foreach ($categories as $category) {
            $categoriesChoices[$category->getId()] = $category->getTitle();
        }

        $data = null;
        if ($this->request->query->has('filter') && array_key_exists('category_filter', $this->request->query->get('filter'))) {
            if ($options['multiple']) {
                $data = array();
                foreach ($this->request->query->get('filter')['category_filter']['category'] as $id => $selectedCategory) {
                    $data[$id] = $selectedCategory;
                }
            } else {
                $data = $this->request->query->get('filter')['category_filter']['tags'];
            }
        }

        $builder
            ->add(
                'category', 'choice', array(
                    'label'       => false,
                    'choices'     => $categoriesChoices,
                    'required'    => false,
                    'expanded'    => true,
                    'empty_value' => 'Tous',
                    'multiple'    => $options['multiple'],
                    'data'        => $data,
                )
            );
    }

    /**
     * Get the filters
     *
     * @param array $filters
     *
     * @return array The filters
     */
    public function getFilters($filters)
    {
        return $this->em->getRepository('VictoireBlogBundle:Category')->findById($filters['category']);
    }

    /**
     * get form name
     * @return string name
     */
    public function getName()
    {
        return 'category_filter';
    }
}
