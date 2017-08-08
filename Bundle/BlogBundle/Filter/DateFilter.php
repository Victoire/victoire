<?php

namespace Victoire\Bundle\BlogBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Victoire\Bundle\FilterBundle\Domain\BaseFilter;
use Victoire\Bundle\PageBundle\Entity\PageStatus;

/**
 * DateFilter form type.
 */
class DateFilter extends BaseFilter
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
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');

        if (isset($parameters['year'])) {
            $qb->andWhere('main_item.status = :article_status_scheduled AND YEAR(main_item.publishedAt) = :year')
                ->orWhere('main_item.status = :article_status_published AND main_item.publishedAt is null AND YEAR(main_item.createdAt) = :year')
                ->orWhere('main_item.status = :article_status_published AND YEAR(main_item.publishedAt) = :year')
                ->setParameter('year', $parameters['year'])
                ->setParameter('article_status_published', PageStatus::PUBLISHED)
                ->setParameter('article_status_scheduled', PageStatus::SCHEDULED);
        }
        if (isset($parameters['month'])) {
            $qb->andWhere('main_item.status = :article_status_scheduled AND MONTH(main_item.publishedAt) = :month')
                ->orWhere('main_item.status = :article_status_published AND main_item.publishedAt is null AND MONTH(main_item.createdAt) = :month')
                ->orWhere('main_item.status = :article_status_published AND MONTH(main_item.publishedAt) = :month')
                ->setParameter('month', $parameters['month'])
                ->setParameter('article_status_published', PageStatus::PUBLISHED)
                ->setParameter('article_status_scheduled', PageStatus::SCHEDULED);
        }
        if (isset($parameters['day'])) {
            $qb->andWhere('main_item.status = :article_status_scheduled AND DAY(main_item.publishedAt) = :day')
                ->orWhere('main_item.status = :article_status_published AND main_item.publishedAt is null AND DAY(main_item.createdAt) = :day')
                ->orWhere('main_item.status = :article_status_published AND DAY(main_item.publishedAt) = :day')
                ->setParameter('day', $parameters['day'])
                ->setParameter('article_status_published', PageStatus::PUBLISHED)
                ->setParameter('article_status_scheduled', PageStatus::SCHEDULED);
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
        $articles = $this->getEntityManager()->getRepository('VictoireBlogBundle:Article')->getAll(true)->run();
        $years = $months = $days = [];
        foreach ($articles as $key => $_article) {
            $years[$_article->getPublishedAt()->format('Y')] = $_article->getPublishedAt()->format('Y');

            if ($options['widget']->getFormat() != 'year') {
                //init $months array
                if (!isset($months[$_article->getPublishedAt()->format('Y')])) {
                    $months[$_article->getPublishedAt()->format('Y')] = [];
                }
                $months[$_article->getPublishedAt()->format('Y')][] = $_article->getPublishedAt()->format('M');
                if ($options['widget']->getFormat() != 'month') {
                    //init $days array
                    if (!isset($days[$_article->getPublishedAt()->format('M')])) {
                        $days[$_article->getPublishedAt()->format('M')] = [];
                    }
                    //assign values
                    $days[$_article->getPublishedAt()->format('M')][] = $_article->getPublishedAt()->format('M');
                }
            }
        }

        ksort($years);

        $data = ['year' => null, 'month' => null, 'day' => null];
        if ($this->getRequest()->query->has('filter') && array_key_exists('date_filter', $this->getRequest()->query->get('filter'))) {
            $_request = $this->getRequest()->query->get('filter')['date_filter'];
            $data = $_request;
        }

        if (in_array($options['widget']->getFormat(), ['year', 'month', 'day'])) {
            if (!$data['year']) {
                // set default value to date filter and set listing to request while not better way
                $data['year'] = $options['widget']->getDefaultValue();
                $this->getRequest()->query->replace(
                    [
                        'filter' => [
                            self::class => [
                                'year' => $options['widget']->getDefaultValue(),
                            ],
                        'listing' => $options['widget']->getListing()->getId(),
                        ],
                    ]
                );
            }
            $builder
                ->add(
                    'year', ChoiceType::class, [
                        'label'       => false,
                        'choices'     => $years,
                        'required'    => false,
                        'expanded'    => false,
                        'multiple'    => false,
                        'empty_value' => false,
                        'data'        => $data['year'],
                    ]
                );
        }
    }

    /**
     * get name.
     *
     * @return string name
     */
    public function getName()
    {
        return 'date_filter';
    }
}
