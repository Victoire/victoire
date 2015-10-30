<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Victoire\Bundle\BlogBundle\Entity\Category;
use Victoire\Bundle\FilterBundle\Filter\BaseFilter;

/**
 * CategoryFilter form type.
 */
class CategoryFilter extends BaseFilter
{
    protected $em;
    protected $request;
    protected $translator;

    /**
     * @param EntityManager                                $em
     * @param \Victoire\Bundle\FilterBundle\Filter\Request $request
     * @param TranslatorInterface                          $translator
     */
    public function __construct(EntityManager $em, $request, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->request = $request;
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
                $parentCategory = $this->em->getRepository('VictoireBlogBundle:Category')->findOneById($parameter);
                $childrenArray = array_merge($childrenArray, $this->getCategoryChildrens($parentCategory, []));
            }
        }

        if (count($childrenArray) > 0) {
            if (array_key_exists('strict', $parameters)) {
                $repository = $this->em->getRepository('VictoireBlogBundle:Article');
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
        $categoryQb = $this->em->getRepository('VictoireBlogBundle:Category')->getAll();
        //getAll published articles
        $articleQb = $this->em->getRepository('VictoireBlogBundle:Article')->getAll(true);

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
        $categoryQb->filterByArticles($articleQb->getInstance('article'));
        $categories = $categoryQb->orderByHierarchy()->getInstance('c_category')->getQuery()->getResult();
        //build the tree for categories
        $tree = $categoryQb->buildTree($categoryQb->getNodesHierarchy());
        $data = null;
        if ($this->request->query->has('filter') && array_key_exists('category_filter', $this->request->query->get('filter'))) {
            if ($options['multiple']) {
                $data = [];
                foreach ($this->request->query->get('filter')['category_filter']['category'] as $id => $selectedCategory) {
                    $data[$id] = $selectedCategory;
                }
            } else {
                $data = $this->request->query->get('filter')['category_filter']['tags'];
            }
        }
        $builder
            ->add(
                'category', 'choice_tree', [
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
        return $this->em->getRepository('VictoireBlogBundle:Category')->findById($filters['category']);
    }

    /**
     * get form name.
     *
     * @return string name
     */
    public function getName()
    {
        return 'category_filter';
    }
}
