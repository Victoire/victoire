<?php
namespace Victoire\Bundle\WidgetBundle\Resolver;

use Victoire\Bundle\WidgetBundle\Model\Widget;
use Symfony\Component\PropertyAccess\PropertyAccess;

class BaseWidgetContentResolver
{

    /**
     * Get the static content of the widget
     *
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetStaticContent(Widget $widget)
    {

        $reflect = new \ReflectionClass($widget);
        $widgetProperties = $reflect->getProperties();
        $parameters = array('widget' => $widget);
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($widgetProperties as $property) {
            $parameters[$property->getName()] = $accessor->getValue($widget, $property->getName());

        }

        return $parameters;
    }

    /**
     * Get the business entity content
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetBusinessEntityContent(Widget $widget)
    {
        $entity = $widget->getEntity();
        $fields = $widget->getFields();
        $parameters = array('widget' => $widget);

        $reflect = new \ReflectionClass($widget);
        $widgetProperties = $reflect->getProperties();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($widgetProperties as $property) {
            $parameters[$property->getName()] = $accessor->getValue($widget, $property->getName());

        }

        if ($entity !== null) {
            //parse the field
            foreach ($fields as $widgetField => $field) {
                //get the value of the field
                $attributeValue =  $entity->getEntityAttributeValue($field);
                
                $parameters[$widgetField] = $attributeValue;
            }
        }

        return $parameters;
    }

    /**
     * Get the content of the widget by the entity linked to it
     *
     * @param Widget $widget
     *
     * @return string
     *
     */
    public function getWidgetEntityContent(Widget $widget)
    {
        $entity = $widget->getEntity();
        $fields = $widget->getFields();
        $parameters = array('widget' => $widget);

        $reflect = new \ReflectionClass($widget);
        $widgetProperties = $reflect->getProperties();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($widgetProperties as $property) {
            $parameters[$property->getName()] = $accessor->getValue($widget, $property->getName());

        }

        if ($entity !== null) {
            //parse the field
            foreach ($fields as $widgetField => $field) {
                //get the value of the field
                $attributeValue =  $entity->getEntityAttributeValue($field);
                
                $parameters[$widgetField] = $attributeValue;
            }
        }

        return $parameters;
    }

    /**
     * Get the content of the widget for the query mode
     *
     * @param Widget $widget
     *
     * @return string
     *
     */
    public function getWidgetQueryContent(Widget $widget)
    {
        $content = '';

        $entities = $this->getWidgetQueryResults($widget);

        foreach ($entities as $entity) {
            $content .= $this->getEntityContent($widget, $entity). ' ';
        }
        return array(
            'widget' => $widget
        );
    }
}
