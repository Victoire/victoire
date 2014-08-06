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
    private $widgetContentResolverChain; // @victoire_widget.widget_content_resolver_chain
    private $request; // @request
    private $filterChain = null; // ?@victoire_core.filter_chain

    public function __construct(
        QueryHelper $queryHelper,
        WidgetHelper $widgetHelper,
        WidgetContentResolverChain $widgetContentResolverChain,
        Request $request,
        $filterChain
    )
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

        $resolver = $this->widgetContentResolverChain->getResolverForWidget($widget);

        switch ($mode) {
            case Widget::MODE_STATIC:
                $parameters = $resolver->getWidgetStaticContent($widget);
                break;
            case Widget::MODE_ENTITY:
                //get the content of the widget with its entity
                $parameters = $resolver->getWidgetEntityContent($widget);
                break;
            case Widget::MODE_BUSINESS_ENTITY:
                //get the content of the widget with its entity
                $parameters = $resolver->getWidgetBusinessEntityContent($widget);
                break;
            case Widget::MODE_QUERY:
                $parameters = $resolver->getWidgetQueryContent($widget);
                break;
            default:
                throw new \Exception('The mode ['.$mode.'] is not supported by the widget manager. Widget ID:['.$widget->getId().']');
        }

        return $parameters;
    }

}
