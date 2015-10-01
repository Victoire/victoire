<?php

namespace Victoire\Bundle\CoreBundle\Event;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * @author Paul Andrieux
 */
class WidgetQueryEvent extends Event
{
    private $widget;
    private $qb;
    private $request;

    /**
     * Constructor.
     *
     * @param Widget       $widget
     * @param QueryBuilder $qb
     * @param Request      $request
     */
    public function __construct(Widget $widget, QueryBuilder $qb, Request $request)
    {
        $this->widget = $widget;
        $this->qb = $qb;
        $this->request = $request;
    }

    /**
     * Get the widget.
     *
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * Get the queryBuilder.
     *
     * @return QueryBuilder
     */
    public function getQb()
    {
        return $this->qb;
    }

    /**
     * Get the request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
