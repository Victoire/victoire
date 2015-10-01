<?php

namespace Victoire\Bundle\WidgetBundle\Resolver\Chain;

use Victoire\Bundle\WidgetBundle\Entity\WidgetItemInterface;

/**
 * WidgetItemChain.
 */
class WidgetItemChain
{
    private $widgetItems;

    public function __construct()
    {
        $this->widgetItems = [];
    }

    public function addWidgetItem(WidgetItemInterface $widgetItem)
    {
        $classname = get_class($widgetItem);
        $parsedClassname = explode('\\', $classname);
        $name = preg_replace('/Widget/', '', end($parsedClassname), 1);
        $newWidgetItem = [
            'class' => $classname,
            'name'  => $name,
            ];
        $this->widgetItems[$name] = $newWidgetItem;
    }

    public function getWidgetItems()
    {
        return $this->widgetItems;
    }
}
