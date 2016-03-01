<?php

namespace Victoire\Bundle\WidgetBundle\Cache;

use Predis\Client;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\SecurityContext;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Entity\WidgetSlotInterface;

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

    public function __construct(Client $redis, AuthorizationChecker $authorizationChecker)
    {
        $this->redis = $redis;
        $this->authorizationChecker = $authorizationChecker;
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
            $this->redis->expire($hash, 7 * 24 * 60 * 1000); // cache for a week
        }
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
