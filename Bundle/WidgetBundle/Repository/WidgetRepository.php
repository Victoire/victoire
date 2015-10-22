<?php

namespace Victoire\Bundle\WidgetBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * The widget Repository.
 */
class WidgetRepository extends EntityRepository
{
    /**
     * Get all the widget within a list of ids.
     *
     * @param array $widgetIds
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllIn(array $widgetIds)
    {
        return $this->createQueryBuilder('widget')
            ->where('widget.id IN (:map)')
            ->setParameter('map', $widgetIds);
    }

    /**
     * Find all the widgets in a list of ids.
     *
     * @param array $widgetIds
     *
     * @return multitype:
     */
    public function findAllIn(array $widgetIds)
    {
        $qb = $this->getAllIn($widgetIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all widgets for a given View.
     *
     * @param View $view
     * @return multitype
     */
    public function findAllWidgetsForView(View $view)
    {
        return $this->findAllIn($view->getWidgetsIds());
    }
}
