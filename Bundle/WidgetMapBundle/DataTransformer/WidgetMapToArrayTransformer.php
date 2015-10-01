<?php

namespace Victoire\Bundle\WidgetMapBundle\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;

class WidgetMapToArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforms an object (WidgetMap) to an array.
     *
     * @param WidgetMap|null $widgetMap
     *
     * @return string
     */
    public function transform($widgetMap)
    {
        if (null === $widgetMap) {
            return [];
        }

        $widgetMapAsArray = [
            'action'            => $widgetMap->getAction(),
            'position'          => $widgetMap->getPosition(),
            'asynchronous'      => $widgetMap->isAsynchronous(),
            'positionReference' => $widgetMap->getPositionReference(),
            'replacedWidgetId'  => $widgetMap->getReplacedWidgetId(),
            'widgetId'          => $widgetMap->getWidgetId(),
        ];

        return $widgetMapAsArray;
    }

    /**
     * Transforms an array to an object (WidgetMap).
     *
     * @param array $widgetMapAsArray
     *
     * @throws TransformationFailedException if object (issue) is not found.
     *
     * @return WidgetMap|null
     */
    public function reverseTransform($widgetMapAsArray)
    {
        if (!$widgetMapAsArray) {
            return;
        }

        $widgetMap = new WidgetMap();
        $widgetMap->setAction(@$widgetMapAsArray['action']);
        $widgetMap->setPosition(@$widgetMapAsArray['position']);
        $widgetMap->setPositionReference(@$widgetMapAsArray['positionReference']);
        $widgetMap->setAsynchronous(@$widgetMapAsArray['asynchronous']);
        $widgetMap->setReplacedWidgetId(@$widgetMapAsArray['replacedWidgetId']);
        $widgetMap->setWidgetId(intval($widgetMapAsArray['widgetId']));

        return $widgetMap;
    }
}
