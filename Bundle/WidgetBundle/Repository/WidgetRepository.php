<?php

namespace Victoire\Bundle\WidgetBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * The Widget Repository.
 */
class WidgetRepository extends EntityRepository
{
    /**
     * Get all Widgets for a given View.
     *
     * @param View $view
     *
     * @return QueryBuilder
     */
    public function getAllForView(View $view)
    {
        //Get all WidgetMaps ids for this View
        $widgetMapsToSearch = [];
        foreach ($view->getBuiltWidgetMap() as $widgetMaps) {
            foreach ($widgetMaps as $widgetMap) {
                $widgetMapsToSearch[] = $widgetMap;
            }
        }

        return $this->createQueryBuilder('widget')
            ->join('widget.widgetMap', 'widgetMap')
            ->andWhere('widgetMap IN (:widgetMaps)')
            ->setParameter('widgetMaps', $widgetMapsToSearch);
    }

    /**
     * Find all Widgets for a given View.
     *
     * @param View $view
     *
     * @return Widget[]
     */
    public function findAllWidgetsForView(View $view)
    {
        $qb = $this->getAllForView($view);

        return $qb->getQuery()->getResult();
    }
}
