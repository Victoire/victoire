<?php

namespace Victoire\Bundle\BlogBundle\Listener;

use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityManager;

/**
 * This class listen Filter widget form changes.
 */
class BlogFilterListener
{
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param unknown       $request
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    /**
     *
     *
     * @param FormEvent $eventArgs
     */
    public function manageExtraFiltersFields(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm()->getParent();
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

        $form->remove('format');
        switch ($data) {
            case 'date_filter':
                $form->remove('multiple');
                $form->add('format', 'choice', array(
                    'label'   => 'widget_filter.form.date.format.label',
                    'choices' => array(
                        'year'  => 'widget_filter.form.date.format.choices.year.label',
                        'month' => 'widget_filter.form.date.format.choices.month.label',
                        'day' => 'widget_filter.form.date.format.choices.day.label',
                    ),
                    'attr' => array(
                        'data-refreshOnChange' => "true",
                    ),
                ));
                $form->add('defaultValue', 'choice', array(
                    'label'   => 'widget_filter.form.date.default.label',
                    'choices' => $years,
                    'empty_value' => 'widget_filter.form.date.default.empty_value.label',
                    )
                );
                break;
            case 'tag_filter':
            case 'category_filter':
                $form->add('multiple', null, array(
                    'label' => 'widget_filter.form.multiple.label',
                ));
                break;
        }
    }
}
