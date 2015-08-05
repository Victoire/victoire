<?php

namespace Victoire\Bundle\BlogBundle\Listener;

use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityManager;
use Victoire\Widget\FilterBundle\Event\WidgetFilterSetDefaultValueEvent;

/**
 * This class listen Filter widget to add defaultValue.
 */
class ArticleFilterDefaultValuesListener
{
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    /**
     *
     *
     * @param FormEvent $event
     */
    public function setDefaultDateValue(WidgetFilterSetDefaultValueEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $businessEntityName = $event->getBusinessEntityName();
        if ($businessEntityName == 'article') {
            $articles = $this->em->getRepository('VictoireBlogBundle:Article')->getAll(true)->run();
            $options = $form->getConfig()->getOptions()['data'];
            $years = $months = $days = array();
            foreach ($articles as $key => $_article) {
                $years[$_article->getPublishedAt()->format('Y')] = $_article->getPublishedAt()->format('Y');

                if ($options->getFormat() != 'year') {
                    //init $months array
                    if (!isset($months[$_article->getPublishedAt()->format('Y')])) {
                        $months[$_article->getPublishedAt()->format('Y')] = array();
                    }
                    $months[$_article->getPublishedAt()->format('Y')][] = $_article->getPublishedAt()->format('M');
                    if ($options->getFormat() != 'month') {
                        //init $days array
                        if (!isset($days[$_article->getPublishedAt()->format('M')])) {
                            $days[$_article->getPublishedAt()->format('M')] = array();
                        }
                        //assign values
                        $days[$_article->getPublishedAt()->format('M')][] = $_article->getPublishedAt()->format('M');
                    }
                }
            }
            $form->add('defaultValue', 'choice', array(
                'label'   => 'widget_filter.form.date.default.label',
                'choices' => $years,
                'empty_value' => 'widget_filter.form.date.default.empty_value.label',
                )
            );
        }

    }
}
