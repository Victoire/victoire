<?php

namespace Victoire\Bundle\BusinessEntityBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

class BusinessEntityAnnotationEvent extends Event
{
    private $businessEntity;
    private $widgets;

    public function __construct(BusinessEntity $businessEntity, $widgets)
    {
        $this->businessEntity = $businessEntity;
        $this->widgets = $widgets;
    }

    /**
     * Get businessEntity.
     *
     * @return BusinessEntity
     */
    public function getBusinessEntity()
    {
        return $this->businessEntity;
    }

    /**
     * Get widgets.
     *
     * @return array
     */
    public function getWidgets()
    {
        return $this->widgets;
    }
}
