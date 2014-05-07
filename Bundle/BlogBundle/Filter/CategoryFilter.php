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
             ->join('item.entity', 'e')
             ->join('e.article', 'a')
             ->join('a.categories', 'c')
             ->andWhere('c.id IN (:categories)')
             ->setParameter('categories', $parameters['categories']);

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
        if ($this->request->query->has('filter')) {
            foreach ($this->request->query->get('filter')['category_filter']['categories'] as $id => $selectedCategory) {
                $selectedCategories[$id] = $selectedCategory;
            }
        }

        $builder
            ->add(
                'categories', 'choice', array(
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

    /**
     * get form name
     * @return string name
     */
    public function getName()
    {
        return 'category_filter';
    }
}
