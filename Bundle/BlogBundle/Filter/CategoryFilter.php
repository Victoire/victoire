<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Victoire\FilterBundle\Filter\BaseFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * CategoryFilter form type
 */
class CategoryFilter extends BaseFilter
{
    protected $em;
    protected $requets;

    public function __construct(EntityManager $em, $request)
    {
        $this->em = $em;
        $this->request = $request;
    }

    public function buildQuery(QueryBuilder &$qb, array $parameters)
    {
        $qb = $qb
             ->join('item.category', 'c')
             ->andWhere('c.id IN (:category)')
             ->setParameter('category', $parameters['category']);

        return $qb;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = $this->em->getRepository('VictoireBlogBundle:Category')->findAll();
        $categoriesChoices = array();
        foreach ($categories as $category) {
            $categoriesChoices[$category->getId()] = $category->getTitle();
        }

        $selectedCategories = array();
        if ($this->request->query->has('filter') && array_key_exists('category_filter', $this->request->query->get('filter'))) {
            foreach ($this->request->query->get('filter')['category_filter']['category'] as $id => $selectedCategory) {
                $selectedCategories[$id] = $selectedCategory;
            }
        }

        $builder
            ->add(
                'category', 'choice', array(
                    'label' => 'blog.category_filter.label',
                    'choices' => $categoriesChoices,
                    'multiple' => true,
                    'attr' => array(
                        'class' => 'select2'
                    ),
                    'data' => $selectedCategories
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false
        ));
    }

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
