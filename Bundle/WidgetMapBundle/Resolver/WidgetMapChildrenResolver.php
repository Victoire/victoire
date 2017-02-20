<?php

namespace Victoire\Bundle\WidgetMapBundle\Resolver;

use Symfony\Bridge\Monolog\Logger;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

class WidgetMapChildrenResolver
{
    private $logger;

    /**
     * WidgetMapChildrenResolver constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Return "after" and "before" children,
     * based on contextual View and its Templates.
     *
     * @return array
     */
    public function getChildren(WidgetMap $widgetMap, View $view = null)
    {
        $positions = [WidgetMap::POSITION_BEFORE, WidgetMap::POSITION_AFTER];
        $children = [];
        foreach ($positions as $position) {
            $matchingChildren = [];

            //Position is null by default
            $children[$position] = null;

            //Pass through all current WidgetMap children for a given position
            foreach ($widgetMap->getContextualChildren($position) as $_child) {
                //If child don't have a substitute for this View and Templates, this is the one
                if (null === $_child->getSubstituteForView($view)) {
                    $children[$position] = $_child;
                    $matchingChildren[] = $_child->getId();
                }
            }

            //If children has not been found for this position
            //and current WidgetMap is a substitute
            if (!$children[$position] && $widgetMap->getReplaced()) {
                //Pass through all replaced WidgetMap children for a given position
                foreach ($widgetMap->getReplaced()->getContextualChildren($position) as $_child) {
                    //If child don't have a substitute for this View and Templates, this is the one
                    if (null === $_child->getSubstituteForView($view)) {
                        $children[$position] = $_child;
                        $matchingChildren[] = $_child->getId();
                    }
                }
            }

            $matchingChildren = array_unique($matchingChildren);
            if (count($matchingChildren) > 1) {
                $this->logger->critical(sprintf(
                    'Conflict found between WidgetMaps %s for View %s',
                    implode(', ', $matchingChildren),
                    $view->getId()
                ));
            }
        }

        return $children;
    }

    /**
     * @param $position
     * @param View|null $view
     *
     * @return bool
     */
    public function hasChildren(WidgetMap $widgetMap, $position, View $view = null)
    {
        foreach ($this->getChildren($widgetMap, $view) as $child) {
            if ($child && $child->getPosition() === $position) {
                return true;
            }
        }

        return false;
    }
}
