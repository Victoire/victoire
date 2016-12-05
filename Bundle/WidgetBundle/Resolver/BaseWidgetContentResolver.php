<?php

namespace Victoire\Bundle\WidgetBundle\Resolver;

use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Victoire\Bundle\WidgetBundle\Model\Widget;

class BaseWidgetContentResolver
{
    protected $queryHelper;
    protected $entityManager;

    /**
     * Get the static content of the widget.
     *
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetStaticContent(Widget $widget)
    {
        $reflect = new \ReflectionClass($widget);
        $widgetProperties = $reflect->getProperties();
        $parameters = ['widget' => $widget];
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($widgetProperties as $property) {
            if (!$property->isStatic()) {
                $value = $accessor->getValue($widget, $property->getName());
                $parameters[$property->getName()] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Get the business entity content.
     *
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetBusinessEntityContent(Widget $widget)
    {
        $entity = $widget->getEntity();
        $parameters = $this->getWidgetStaticContent($widget);

        $this->populateParametersWithWidgetFields($widget, $entity, $parameters);

        return $parameters;
    }

    /**
     * Get the content of the widget by the entity linked to it.
     *
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetEntityContent(Widget $widget)
    {
        $entity = $widget->getEntity();

        $parameters = $this->getWidgetStaticContent($widget);

        $this->populateParametersWithWidgetFields($widget, $entity, $parameters);

        return $parameters;
    }

    /**
     * Get the content of the widget for the query mode.
     *
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetQueryContent(Widget $widget)
    {
        $parameters = $this->getWidgetStaticContent($widget);

        $entity = $this->getWidgetQueryBuilder($widget)
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();

        $this->populateParametersWithWidgetFields($widget, $entity, $parameters);

        return $parameters;
    }

    /**
     * Get the widget query result.
     *
     * @param Widget $widget The widget
     *
     * @return \Doctrine\ORM\QueryBuilder The list of entities
     */
    public function getWidgetQueryBuilder(Widget $widget)
    {
        //get the base query
        $itemsQueryBuilder = $this->queryHelper->getQueryBuilder($widget, $this->entityManager);

        // Filter only visibleOnFront
        $itemsQueryBuilder->andWhere('main_item.visibleOnFront = true');

        //add the query of the widget
        return $this->queryHelper->buildWithSubQuery($widget, $itemsQueryBuilder, $this->entityManager);
    }

    protected function populateParametersWithWidgetFields(Widget $widget, $entity, &$parameters)
    {
        $fields = $widget->getFields();
        //parse the field
        foreach ($fields as $widgetField => $field) {
            //get the value of the field
            if ($entity !== null) {
                $attributeValue = $entity->getEntityAttributeValue($field);
            } else {
                $attributeValue = $widget->getBusinessEntityName().' -> '.$field;
            }

            $parameters[$widgetField] = $attributeValue;
        }

        $widget->setEntity($entity);
    }

    /**
     * @param QueryHelper $queryHelper
     */
    public function setQueryHelper(QueryHelper $queryHelper)
    {
        $this->queryHelper = $queryHelper;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
