<?php
namespace Victoire\Bundle\WidgetBundle\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Model\Widget;
use Victoire\Bundle\WidgetBundle\Resolver\Chain\WidgetContentResolverChain;

class WidgetContentResolver
{

    private $queryHelper; // @victoire_query.query_helper
    private $widgetHelper; // @victoire_query.widget_helper
    private $filterChain = null; // ?@victoire_core.filter_chain
    private $widgetContentResolverChain; // @victoire_widget.widget_content_resolver_chain
    private $request; // @request

    public function __construct(QueryHelper $queryHelper, WidgetHelper $widgetHelper, WidgetContentResolverChain $widgetContentResolverChain, Request $request, $filterChain)
    {
        $this->queryHelper = $queryHelper;
        $this->widgetHelper = $widgetHelper;
        $this->filterChain = $filterChain;
        $this->widgetContentResolverChain = $widgetContentResolverChain;
        $this->request = $request;
    }

    /**
     * Get content for the widget
     *
     * @param Widget $widget
     *
     * @return Ambigous   <string, unknown, \Victoire\Bundle\CoreBundle\Widget\Managers\mixed, mixed>
     * @throws \Exception
     */
    public function getWidgetContent(Widget $widget)
    {
        //the mode of display of the widget
        $mode = $widget->getMode();

        //the widget must have a mode
        if ($mode === null) {
            throw new \Exception('The widget ['.$widget->getId().'] has no mode.');
        }

        //the content of the widget
        $content = '';
        $widgetName = $this->widgetHelper->getWidgetName($widget);
        if ($resolver = $this->widgetContentResolverChain->hasResolver($widgetName)) {

            switch ($mode) {
                case Widget::MODE_STATIC:
                    $content = $resolver->getWidgetStaticContent($widget);
                    break;
                case Widget::MODE_ENTITY:
                    //get the content of the widget with its entity
                    $content = $resolver->getWidgetEntityContent($widget);
                    break;
                case Widget::MODE_BUSINESS_ENTITY:
                    //get the content of the widget with its entity
                    $content = $resolver->getWidgetBusinessEntityContent($widget);
                    break;
                case Widget::MODE_QUERY:
                    $content = $resolver->getWidgetQueryContent($widget);
                    break;
                default:
                    throw new \Exception('The mode ['.$mode.'] is not supported by the widget manager. Widget ID:['.$widget->getId().']');
            }
        }

        return $content;
    }

    /**
     * Get the widget query result
     *
     * @param Widget $widget The widget
     *
     * @return array The list of entities
     */
    protected function getWidgetQueryResults(Widget $widget)
    {
        //get the base query
        $itemsQueryBuilder = $this->queryHelper->getQueryBuilder($widget);

        // add this fake condition to ensure that there is always a "where" clause.
        // In query mode, usage of "AND" will be always valid instead of "WHERE"
        $itemsQueryBuilder->andWhere('1 = 1');

        if ($this->filterChain) {
            $filters = $this->request->query->get('victoire_form_filter');

            //the id is an integer
            $listId = intval($filters['listing']);

            //if the filters is the widget id
            if ($listId === $widget->getId()) {
                unset($filters['listing']);

                //we parse the filters
                foreach ($this->filterChains->getFilters() as $name => $filter) {
                    if (!empty($filters[$name])) {
                        $filter->buildQuery($itemsQueryBuilder, $filters[$name]);
                        $widget->filters[$name] = $filter->getFilters($filters[$name]);
                    }
                }
            }
        }

        //add the query of the widget
        $items = $queryHelper->buildWithSubQuery($widget, $itemsQueryBuilder)
            ->getQuery()
            ->getResult();

        return $items;
    }

}
