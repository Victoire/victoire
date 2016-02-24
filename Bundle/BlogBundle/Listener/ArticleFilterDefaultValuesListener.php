<?php

namespace Victoire\Bundle\BlogBundle\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Victoire\Widget\FilterBundle\Event\WidgetFilterSetDefaultValueEvent;

/**
 * This class listen Filter widget to add defaultValue.
 */
class ArticleFilterDefaultValuesListener
{
    protected $entityManager;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormEvent $event
     */
    public function setDefaultDateValue(WidgetFilterSetDefaultValueEvent $event)
    {
        $form = $event->getForm();
        $businessEntityId = $event->getBusinessEntityId();
        if ($businessEntityId == 'article') {
            $articles = $this->entityManager->getRepository('VictoireBlogBundle:Article')->getAll(true)->run();
            $options = $form->getConfig()->getOptions()['data'];
            $years = $months = $days = [];
            foreach ($articles as $key => $_article) {
                $years[] = $_article->getPublishedAt()->format('Y');

                if ($options->getFormat() != 'year') {
                    //init $months array
                    if (!isset($months[$_article->getPublishedAt()->format('Y')])) {
                        $months[$_article->getPublishedAt()->format('Y')] = [];
                    }
                    $months[$_article->getPublishedAt()->format('Y')][] = $_article->getPublishedAt()->format('M');
                    if ($options->getFormat() != 'month') {
                        //init $days array
                        if (!isset($days[$_article->getPublishedAt()->format('M')])) {
                            $days[$_article->getPublishedAt()->format('M')] = [];
                        }
                        //assign values
                        $days[$_article->getPublishedAt()->format('M')][] = $_article->getPublishedAt()->format('M');
                    }
                }
            }
            $form->add('defaultValue', ChoiceType::class, [
                'label' => 'widget_filter.form.date.default.label',
                'choices' => $years,
                'placeholder' => 'widget_filter.form.date.default.empty_value.label',
            ]);
        }
    }
}
