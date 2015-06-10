<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Victoire\Bundle\FilterBundle\Filter\BaseFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * TagFilter form type
 */
class TagFilter extends BaseFilter
{
    protected $em;
    protected $request;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param unknown       $request
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
        //clean the parameters from the blank value
        foreach ($parameters['tags'] as $index => $parameter) {
            //the blank value is removed
            if ($parameter === '') {
                unset($parameters['tags'][$index]);
            }
        }

        if (count($parameters['tags']) > 0) {
            $qb = $qb
                    ->join('main_item.tags', 't')
                    ->andWhere('t.id IN (:tags)')
                    ->setParameter('tags', $parameters['tags']);
        }

        return $qb;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //getAll tags
        $tagQb = $this->em->getRepository('VictoireBlogBundle:Tag')->getAll();
        //getAll published articles
        $articleQb = $this->em->getRepository('VictoireBlogBundle:Article')->getAll(true);

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
        $tagsChoices = array();

        foreach ($tags as $tag) {
            $tagsChoices[$tag->getId()] = $tag->getTitle();
        }

        $data = null;
        if ($this->request->query->has('filter') && array_key_exists('tag_filter', $this->request->query->get('filter'))) {
            if ($options['multiple']) {
                $data = array();
                foreach ($this->request->query->get('filter')['tag_filter']['tags'] as $id => $selectedTag) {
                    $data[$id] = $selectedTag;
                }
            } else {
                $data = $this->request->query->get('filter')['tag_filter']['tags'];
            }
        }

        $builder
            ->add(
                'tags', 'choice', array(
                    'label' => false,
                    'choices' => $tagsChoices,
                    'required' => false,
                    'expanded' => true,
                    'multiple' => $options['multiple'],
                    'data' => $data,
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
        return $this->em->getRepository('VictoireBlogBundle:Tag')->findById($filters['tags']);
    }

    /**
     * get form name
     * @return string name
     */
    public function getName()
    {
        return 'tag_filter';
    }
}
