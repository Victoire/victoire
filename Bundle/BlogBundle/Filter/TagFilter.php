<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Victoire\Bundle\FilterBundle\Filter\BaseFilter;

/**
 * TagFilter form type.
 */
class TagFilter extends BaseFilter
{
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

        //Handle single tag
        if (!is_array($parameters['tags'])) {
            $parameters['tags'] = [$parameters['tags']];
        }

        //clean the parameters from the blank value
        foreach ($parameters['tags'] as $index => $parameter) {
            //the blank value is removed
            if ($parameter === '') {
                unset($parameters['tags'][$index]);
            }
        }

        if (count($parameters['tags']) > 0) {
            if (array_key_exists('strict', $parameters)) {
                $repository = $this->getEntityManager()->getRepository('VictoireBlogBundle:Article');
                foreach ($parameters['tags'] as $index => $tag) {
                    $parameter = ':tag'.$index;
                    $subquery = $repository->createQueryBuilder('article_'.$index)
                                ->join('article_'.$index.'.tags', 'tag_'.$index)
                                ->where('tag_'.$index.' = '.$parameter);
                    $qb->andWhere($qb->expr()->in('main_item', $subquery->getDql()))
                                ->setParameter($parameter, $tag);
                }
            } else {
                $qb = $qb
                        ->join('main_item.tags', 't')
                        ->andWhere('t.id IN (:tags)')
                        ->setParameter('tags', $parameters['tags']);
            }
        }

        return $qb;
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
        //getAll tags
        $tagQb = $this->getEntityManager()->getRepository('VictoireBlogBundle:Tag')->getAll();
        //getAll published articles
        $articleQb = $this->getEntityManager()->getRepository('VictoireBlogBundle:Article')->getAll(true);

        //get Listing
        $listing = $options['widget']->getListing();

        $mode = $listing->getMode();
        switch ($mode) {
            case 'query':                //filter with listingQuery
                $articleQb->filterWithListingQuery($listing->getQuery());
                break;
        }
        //filter tags with right articles
        $tagQb->filterByArticles($articleQb->getInstance('article'));
        $tags = $tagQb->getInstance('t_tag')->getQuery()->getResult();
        //the blank value
        $tagsChoices = [];

        foreach ($tags as $tag) {
            $tagsChoices[$tag->getTitle()] = $tag->getId();
        }

        $data = null;
        if ($this->getRequest()->query->has('filter') && array_key_exists('tag_filter', $this->getRequest()->query->get('filter'))) {
            if ($options['multiple']) {
                $data = [];
                foreach ($this->getRequest()->query->get('filter')['tag_filter']['tags'] as $id => $selectedTag) {
                    $data[$id] = $selectedTag;
                }
            } else {
                $data = $this->getRequest()->query->get('filter')['tag_filter']['tags'];
            }
        }

        $builder
            ->add('tags', ChoiceType::class, [
                'label'             => false,
                'choices'           => $tagsChoices,
                'choices_as_values' => true,
                'empty_value'       => 'blog.tag_filter.empty_value',
                'required'          => false,
                'expanded'          => true,
                'multiple'          => $options['multiple'],
                'data'              => $data,
            ]);
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
        return $this->getEntityManager()->getRepository('VictoireBlogBundle:Tag')->findById($filters['tags']);
    }

    /**
     * get name.
     *
     * @return string name
     */
    public function getName()
    {
        return 'tag_filter';
    }
}
