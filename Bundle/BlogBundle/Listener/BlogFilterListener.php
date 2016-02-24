<?php

namespace Victoire\Bundle\BlogBundle\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Victoire\Bundle\BlogBundle\Filter\CategoryFilter;
use Victoire\Bundle\BlogBundle\Filter\DateFilter;
use Victoire\Bundle\BlogBundle\Filter\TagFilter;
use Victoire\Widget\FilterBundle\Event\WidgetFilterSetDefaultValueEvent;

/**
 * This class listen Filter widget form changes.
 */
class BlogFilterListener
{
    protected $em;

    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param FormEvent $event
     */
    public function manageExtraFiltersFields(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm()->getParent();

        $form->remove('format');
        $form->remove('defaultValue');
        $eventDefaultValue = null;
        switch ($data) {
            case DateFilter::class:
                $form->remove('multiple');
                $form->add('format', ChoiceType::class, [
                    'label'   => 'widget_filter.form.date.format.label',
                    'choices' => [
                        'widget_filter.form.date.format.choices.year.label'  => 'year',
                        'widget_filter.form.date.format.choices.month.label' => 'month',
                        'widget_filter.form.date.format.choices.day.label'   => 'day',
                    ],
                    'choices_as_values' => true,
                    'attr'              => [
                        'data-refreshOnChange' => 'true',
                    ],
                ]);
                $eventDefaultValue = 'victoire.widget_filter.form.date.set_default_value';
                break;
            case TagFilter::class:
                $form->add('multiple', null, [
                    'label' => 'widget_filter.form.multiple.label',
                ]);
                $eventDefaultValue = 'victoire.widget_filter.form.tag.set_default_value';
            break;
            case CategoryFilter::class:
                $form->add('multiple', null, [
                    'label' => 'widget_filter.form.multiple.label',
                ]);
                $eventDefaultValue = 'victoire.widget_filter.form.category.set_default_value';
                break;
        }
        if ($eventDefaultValue) {
            $defaultValueEvent = new WidgetFilterSetDefaultValueEvent($form, $data);
            $this->eventDispatcher->dispatch($eventDefaultValue, $defaultValueEvent);
        }
    }
}
