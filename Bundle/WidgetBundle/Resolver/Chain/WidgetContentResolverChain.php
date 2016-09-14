<?php

namespace Victoire\Bundle\WidgetBundle\Resolver\Chain;

use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Resolver\BaseWidgetContentResolver;

class WidgetContentResolverChain
{
    private $resolvers;
    private $widgetHelper;

    public function __construct(WidgetHelper $widgetHelper)
    {
        $this->resolvers = [];
        $this->widgetHelper = $widgetHelper;
    }

    public function addResolver($alias, $resolver)
    {
        $this->resolvers[$alias] = $resolver;
    }

    public function getResolvers()
    {
        return $this->resolvers;
    }

    public function hasResolverForWidget(Widget $widget)
    {
        $alias = $this->widgetHelper->getWidgetName($widget);

        return $this->hasResolver($alias);
    }

    public function hasResolver($alias)
    {
        if (array_key_exists($alias, $this->resolvers)) {
            return true;
        }

        return false;
    }

    /**
     * @param Widget $widget
     *
     * @return BaseWidgetContentResolver
     * @throws \Exception
     */
    public function getResolverForWidget(Widget $widget)
    {
        $alias = $this->widgetHelper->getWidgetName($widget);

        return $this->getResolver($alias);
    }

    public function getResolver($alias)
    {
        if (array_key_exists($alias, $this->resolvers)) {
            return $this->resolvers[$alias];
        } else {
            throw new \InvalidArgumentException(sprintf('The "%s" resolver does not exist', $alias));
        }
    }
}
