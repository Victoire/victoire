<?php

namespace Victoire\Bundle\WidgetBundle\Resolver\Chain;

use Victoire\Bundle\WidgetBundle\Entity\WidgetItemInterface;
/**
* WidgetItemChain
*/
class WidgetItemChain
{
    private $widgetItems;

    function __construct()
    {
        $this->widgetItems = array();
    }

    public function addWidgetItem(WidgetItemInterface $widgetItem)
    {
        $classname = get_class($widgetItem);
        $parsedClassname = explode( '\\', $classname);
        $name = preg_replace('/Widget/', '', end($parsedClassname), 1);
        $newWidgetItem = array(
            'class' => $classname,
            'name' => $name
            );
        $this->widgetItems[$name] = $newWidgetItem;

    }

    public function getWidgetItems()
    {
        return $this->widgetItems;
    }
}