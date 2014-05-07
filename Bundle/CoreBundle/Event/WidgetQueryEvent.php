<?php
namespace Victoire\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Entity\Widget;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;


class WidgetQueryEvent extends Event
{
    private $widget;
    private $qb;
    private $request;

    public function __construct(Widget &$widget, QueryBuilder &$qb, Request $request)
    {
        $this->widget = $widget;
        $this->qb = $qb;
        $this->request = $request;
    }

    public function getWidget()
    {
        return $this->widget;
    }

    public function getQb()
    {
        return $this->qb;
    }

    public function getRequest()
    {
        return $this->request;
    }

}
