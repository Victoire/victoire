<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Victoire\Bundle\BlogBundle\Entity\BlogCategory;
use Victoire\Bundle\FilterBundle\Domain\FilterFormFieldQueryHandler;
use Victoire\Bundle\FilterBundle\Filter\BaseFilter;

/**
 * Class CategoryFilter.
 */
class CategoryFilter extends BaseFilter
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * CategoryFilter constructor.
     *
     * @param EntityManager               $entityManager
     * @param RequestStack                $requestStack
     * @param FilterFormFieldQueryHandler $filterQueryHandler
     * @param TranslatorInterface         $translator
     */
    public function __construct(
        EntityManager $entityManager,
        RequestStack $requestStack,
        FilterFormFieldQueryHandler $filterQueryHandler,
        TranslatorInterface $translator
    ) {
        parent::__construct($entityManager, $requestStack, $filterQueryHandler);
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
                $parentCategory = $this->getEntityManager()->getRepository('VictoireBlogBundle:BlogCategory')->findOneById($parameter);
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

    public function getCategoryChildrens(BlogCategory $category, $childrenArray)
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
        $categories = $this->filterQueryHandler->handle($options['widget'], BlogCategory::class);

        $categoriesChoices = [];

        foreach ($categories as $category) {
            $categoriesChoices[$category->getId()] = $category->getTitle();
        }

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
                'category', ChoiceType::class, [
                    'label'       => false,
                    'choices'     => $categoriesChoices,
                    'required'    => false,
                    'expanded'    => true,
                    'empty_value' => $this->translator->trans('blog.category_filter.empty_value.label'),
                    'data'        => $data,
                ]
            );
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
        return $this->getEntityManager()->getRepository('VictoireBlogBundle:BlogCategory')->findById($filters['category']);
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
