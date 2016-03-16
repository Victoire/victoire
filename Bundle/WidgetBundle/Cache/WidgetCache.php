<?php

namespace Victoire\Bundle\WidgetBundle\Cache;

use Predis\Client;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Entity\WidgetSlotInterface;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;

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
    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;
    /**
     * @var WidgetHelper
     */
    private $widgetHelper;

    /**
     * WidgetCache constructor.
     *
     * @param Client               $redis
     * @param AuthorizationChecker $authorizationChecker
     * @param WidgetHelper         $widgetHelper
     */
    public function __construct(Client $redis, AuthorizationChecker $authorizationChecker, WidgetHelper $widgetHelper)
    {
        $this->redis = $redis;
        $this->authorizationChecker = $authorizationChecker;
        $this->widgetHelper = $widgetHelper;
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
        if ($hash) {
            $this->redis->set($hash, $content);
            $this->redis->expire($hash, $this->widgetHelper->getCacheTimeout($widget)); // cache for a week
        }
    }

    /**
     * clear all redis cache.
     */
    public function clear()
    {
        $this->redis->executeCommand($this->redis->createCommand('FLUSHALL'));
    }

    /**
     * @param Widget $widget
     *
     * @return string
     */
    protected function getHash(Widget $widget)
    {
        $hash = null;
        if (!$widget instanceof WidgetSlotInterface) {
            if ($widget->getMode() == Widget::MODE_BUSINESS_ENTITY
                && ($entity = $widget->getEntity())
                && method_exists($widget->getEntity(), 'getUpdatedAt')) {
                $hash = $this->generateBusinessEntityHash($widget);
            } elseif ($widget->getMode() == Widget::MODE_STATIC) {
                $hash = $this->generateHash($widget);
            }
        }

        return $hash;
    }

    protected function generateBusinessEntityHash(Widget $widget)
    {
        return sprintf('%s-%s-%s--%s-%s-%s',
            $widget->getId(),
            $widget->getUpdatedAt()->getTimestamp(),
            $widget->getCurrentView()->getReference()->getId(),
            $widget->getEntity()->getId(),
            $widget->getEntity()->getUpdatedAt()->getTimestamp(),
            (string) $this->authorizationChecker->isGranted('ROLE_VICTOIRE')
        );
    }

    private function generateHash($widget)
    {
        return sprintf('%s-%s-%s-%s',
            $widget->getId(),
            $widget->getUpdatedAt()->getTimestamp(),
            $widget->getCurrentView()->getReference()->getId(),
            (string) $this->authorizationChecker->isGranted('ROLE_VICTOIRE')
        );
    }
}
