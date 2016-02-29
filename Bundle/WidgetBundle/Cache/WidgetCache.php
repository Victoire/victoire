<?php

namespace Victoire\Bundle\WidgetBundle\Cache;

use Predis\Client;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * This class handle the saving of Widgets.
 * Widgets are stored for a week, but are invalidated as soon as
 * the Widget's or BusinessEntity's updatedAt field is changed.
 */
class WidgetCache
{
    /**
     * @var Client
     */
    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param Widget $widget
     *
     * @return string
     */
    public function fetch(Widget $widget)
    {
        return $this->redis->get($this->getHash($widget));
    }

    /**
     * @param Widget $widget
     * @param        $content
     */
    public function save(Widget $widget, $content)
    {
        $hash = $this->getHash($widget);

        $this->redis->set($hash, $content);
        $this->redis->expire($hash, 7 * 24 * 60 * 1000); // cache for a week
    }

    /**
     * @param Widget $widget
     *
     * @return string
     */
    protected function getHash(Widget $widget)
    {
        $hash = sprintf('%s-%s', $widget->getId(), $widget->getUpdatedAt()->getTimestamp());

        if ($widget->getMode() == Widget::MODE_BUSINESS_ENTITY
            && ($entity = $widget->getEntity())
            && method_exists($widget->getEntity(), 'getUpdatedAt')) {
            $hash .= sprintf('-%s', $entity->getUpdatedAt()->getTimestamp());
        }

        return $hash;
    }


}
