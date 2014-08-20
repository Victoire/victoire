<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Victoire\Widget\FilterBundle\Filter\BaseFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * CategoryFilter form type
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
     * @return queryBuilder
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
        $tags = $this->em->getRepository('VictoireBlogBundle:Tag')->findAll();

        //the blank value
        $tagsChoices = array(null => '');

        foreach ($tags as $tag) {
            $tagsChoices[$tag->getId()] = $tag->getTitle();
        }

        $selectedTags = array();
        if ($this->request->query->has('filter') && array_key_exists('tag_filter', $this->request->query->get('filter'))) {
            foreach ($this->request->query->get('filter')['tag_filter']['tags'] as $id => $selectedTag) {
                $selectedTags[$id] = $selectedTag;
            }
        }

        $builder
            ->add(
                'tags', 'choice', array(
                    'label' => 'blog.tag_filter.label',
                    'choices' => $tagsChoices,
                    'required' => false,
                    'multiple' => true,
                    'attr' => array(
                        'class' => 'select2'
                    ),
                    'data' => $selectedTags
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
