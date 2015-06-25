<?php

namespace Victoire\Bundle\BlogBundle\Listener;

use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Victoire\Widget\FilterBundle\Event\WidgetFilterSetDefaultValueEvent;

/**
 * This class listen Filter widget form changes.
 */
class BlogFilterListener
{
    protected $em;

    private $eventDispatcher;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }
    /**
     *
     *
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
                $eventDefaultValue = 'victoire.widget_filter.form.date.set_default_value';
                break;
            case 'tag_filter':
                $form->add('multiple', null, array(
                    'label' => 'widget_filter.form.multiple.label',
                ));
                $eventDefaultValue = 'victoire.widget_filter.form.tag.set_default_value';
            break;
            case 'category_filter':
                $form->add('multiple', null, array(
                    'label' => 'widget_filter.form.multiple.label',
                ));
                $eventDefaultValue = 'victoire.widget_filter.form.category.set_default_value';
                break;
        }
        if ($eventDefaultValue) {
            $defaultValueEvent = new WidgetFilterSetDefaultValueEvent($form, $data);
            $this->eventDispatcher->dispatch($eventDefaultValue, $defaultValueEvent);
        }

    }
}
