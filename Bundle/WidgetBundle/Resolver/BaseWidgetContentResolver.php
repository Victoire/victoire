<?php

namespace Victoire\Bundle\WidgetBundle\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Victoire\Bundle\WidgetBundle\Model\Widget;
use Victoire\Widget\FilterBundle\Filter\Chain\FilterChain;

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
        $plop;
        foreach ($widgetProperties as $property) {
            $value = $accessor->getValue($widget, $property->getName());
            $parameters[$property->getName()] = $value;

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
        $parameters = $this->getWidgetStaticContent($widget);

        $this->populateParametersWithWidgetFields($widget, $entity, $parameters);

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

        $parameters = $this->getWidgetStaticContent($widget);

        $this->populateParametersWithWidgetFields($widget, $entity, $parameters);

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

        $parameters = $this->getWidgetStaticContent($widget);

        $entity = $this->getWidgetQueryBuilder($widget)
                       ->setMaxResults(1)
                       ->getQuery()
                       ->getOneOrNullResult();

        $fields = $widget->getFields();
        $this->populateParametersWithWidgetFields($widget, $entity, $parameters);

        return $parameters;
    }

    /**
     * Get the widget query result
     *
     * @param Widget $widget The widget
     *
     * @return array The list of entities
     */
    public function getWidgetQueryBuilder(Widget $widget)
    {
        $queryHelper = $this->queryHelper;

        //get the base query
        $itemsQueryBuilder = $queryHelper->getQueryBuilder($widget);

        if ($this->filterChain !== null) {
            $request = $this->request;
            $filters = $request->query->get('victoire_form_filter');

            //the id is an integer
            $listId = intval($filters['listing']);

            //if the filters is the widget id
            if ($listId === $widget->getId()) {
                unset($filters['listing']);

                $filterChains = $this->filterChain;

                //we parse the filters
                foreach ($filterChains->getFilters() as $name => $filter) {
                    if (!empty($filters[$name])) {
                        $filter->buildQuery($itemsQueryBuilder, $filters[$name]);
                        $widget->filters[$name] = $filter->getFilters($filters[$name]);
                    }
                }
            }
        }

        //add the query of the widget
        return $queryHelper->buildWithSubQuery($widget, $itemsQueryBuilder);
    }

    protected function populateParametersWithWidgetFields(Widget $widget, $entity, &$parameters)
    {
        $fields = $widget->getFields();
        //parse the field
        foreach ($fields as $widgetField => $field) {
            //get the value of the field
            if ($entity !== null) {
                $attributeValue =  $entity->getEntityAttributeValue($field);
            } else {
                $attributeValue = $widget->getBusinessEntityName() . ' -> ' . $field;
            }

            $parameters[$widgetField] = $attributeValue;
        }
    }

    public function setQueryHelper(QueryHelper $queryHelper)
    {
        $this->queryHelper = $queryHelper;
    }
    public function setFilterChain($filterChain)
    {
        $this->filterChain = $filterChain;
    }
    public function setRequest($request)
    {
        $this->request = $request;
    }
}
