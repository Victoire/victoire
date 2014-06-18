<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Victoire\Widget\FilterBundle\Filter\BaseFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
     * @param unknown $request
     */
    public function __construct(EntityManager $em, $request)
    {
        $this->em = $em;
        $this->request = $request;
    }

    /**
     * (non-PHPdoc)
     * @see \Victoire\Widget\FilterBundle\Filter\BaseFilter::buildQuery()
     */
    public function buildQuery(QueryBuilder &$qb, array $parameters)
    {
        $qb = $qb
             ->join('item.tags', 't')
             ->andWhere('t.id IN (:tags)')
             ->setParameter('tags', $parameters['tags']);

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
        $tags = $this->em->getRepository('VictoireBlogBundle:Tag')->findAll();
        $tagsChoices = array();
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
                    'multiple' => true,
                    'attr' => array(
                        'class' => 'select2'
                    ),
                    'data' => $selectedTags
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
