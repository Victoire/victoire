<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Victoire\Bundle\BlogBundle\Entity\Category;
use Victoire\Bundle\FilterBundle\Filter\BaseFilter;

/**
 * CategoryFilter form type.
 */
class CategoryFilter extends BaseFilter
{
    protected $translator;

    /**
     * @param EntityManager       $entityManager
     * @param Request             $request
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManager $entityManager, Request $request, TranslatorInterface $translator)
    {
        parent::__construct($entityManager, $request);
        $this->translator = $translator;
    }

    /**
     * Build the query.
     *
     * @param QueryBuilder &$qb
     * @param array        $parameters
     *
     * @return QueryBuilder
     */
    public function buildQuery(QueryBuilder $qb, array $parameters)
    {
        if (!is_array($parameters['category'])) {
            $parameters['category'] = [$parameters['category']];
        }
        $childrenArray = [];
        //clean the parameters from the blank value
        foreach ($parameters['category'] as $index => $parameter) {
            //the blank value is removed
            if ($parameter === '') {
                unset($parameters['category'][$index]);
            } else {
                $parentCategory = $this->getEntityManager()->getRepository('VictoireBlogBundle:Category')->findOneById($parameter);
                $childrenArray = array_merge($childrenArray, $this->getCategoryChildrens($parentCategory, []));
            }
        }

        if (count($childrenArray) > 0) {
            if (array_key_exists('strict', $parameters)) {
                $repository = $this->getEntityManager()->getRepository('VictoireBlogBundle:Article');
                foreach ($childrenArray as $index => $category) {
                    $parameter = ':category'.$index;
                    $subquery = $repository->createQueryBuilder('article_'.$index)
                        ->join('article_'.$index.'.category', 'category_'.$index)
                        ->where('category_'.$index.' = '.$parameter);
                    $qb->andWhere($qb->expr()->in('main_item', $subquery->getDql()))
                        ->setParameter($parameter, $category);
                }
            } else {
                $qb = $qb
                    ->join('main_item.category', 'c')
                    ->andWhere('c.id IN (:category)')
                    ->setParameter('category', $childrenArray);
            }
        }

        return $qb;
    }

    public function getCategoryChildrens(Category $category, $childrenArray)
    {
        $childrenArray[] = $category->getId();
        $childrens = $category->getChildren();

        foreach ($childrens as $children) {
            $childrenArray = $this->getCategoryChildrens($children, $childrenArray);
        }

        return $childrenArray;
    }

    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //getAll categories
        $categoryQb = $this->getEntityManager()->getRepository('VictoireBlogBundle:Category')->getAll();
        //getAll published articles
        $articleQb = $this->getEntityManager()->getRepository('VictoireBlogBundle:Article')->getAll(true);

        //get Listing
        $listing = $options['widget']->getListing();

        $mode = $listing->getMode();
        switch ($mode) {
            case 'query':
                //filter with listingQuery
                $articleQb->filterWithListingQuery($listing->getQuery());
                break;
        }
        //filter categoriess with right articles
        $categories = $categoryQb->filterByArticles(
                        $articleQb->getInstance()
                    )
                    ->orderByHierarchy()
                    ->getInstance('c_category')
                    ->getQuery()
                    ->getResult();
        //build the tree for categories
        $tree = $categoryQb->buildTree($categoryQb->getNodesHierarchy());
        $categoryQb->clearInstance();
        $articleQb->clearInstance();
        $data = null;
        if ($this->getRequest()->query->has('filter') && array_key_exists('category_filter', $this->getRequest()->query->get('filter'))) {
            if ($options['multiple']) {
                $data = [];
                foreach ($this->getRequest()->query->get('filter')['category_filter']['category'] as $id => $selectedCategory) {
                    $data[$id] = $selectedCategory;
                }
            } else {
                $data = $this->getRequest()->query->get('filter')['category_filter']['category'];
            }
        }
        $builder
            ->add(
                'category', 'infinite_form_choice_tree', [
                    'label'       => false,
                    'choices'     => $this->buildHierarchy($tree, $categories),
                    'required'    => false,
                    'expanded'    => true,
                    'empty_value' => $this->translator->trans('blog.category_filter.empty_value.label'),
                    'multiple'    => false,
                    'data'        => $data,
                ]
            );
    }

    /**
     * @param $categories
     * @param $validCategories
     *
     * @return array
     */
    public function buildHierarchy($categories, $validCategories)
    {
        $hierarchy = [];
        foreach ($categories as $category) {
            $isValid = false;
            $categoryHierarchy = [];
            //if we have children we try to build their hierarchy
            if (count($children = $category['__children'])) {
                $categoryHierarchy = $this->buildHierarchy($children, $validCategories);
            }
            // try to match with listed categories
            foreach ($validCategories as $key => $validCategory) {
                if ($validCategory->getId() == $category['id']) {
                    $isValid = true;
                    //unset the valid category
                    unset($validCategories[$key]);
                }
            }
            // if the current category is valid or if a children is valid
            if ($isValid || count($categoryHierarchy) > 0) {
                //add a node
                $node = [];
                $node['label'] = $category['title'];
                $node['value'] = $category['id'];
                $node['choice_list'] = $categoryHierarchy;
                $hierarchy[] = $node;
            }
        }

        return $hierarchy;
    }

    /**
     * Get the filters.
     *
     * @param array $filters
     *
     * @return array The filters
     */
    public function getFilters($filters)
    {
        return $this->getEntityManager()->getRepository('VictoireBlogBundle:Category')->findById($filters['category']);
    }

    /**
     * get name.
     *
     * @return string name
     */
    public function getName()
    {
        return 'category_filter';
    }
}
