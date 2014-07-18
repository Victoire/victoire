<?php
namespace Victoire\Bundle\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 *
 * @author Paul Andrieux
 *
 */
class WidgetRepository extends EntityRepository
{
    /**
     * Get all the widget within a list of ids
     *
     * @param array $widgetIds
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllIn(array $widgetIds)
    {
        return $this->createQueryBuilder('widget')
            ->where('widget.id IN (:map)')
            ->setParameter('map', $widgetIds);
    }

    /**
     * Find all the widgets in a list of ids
     *
     * @param array $widgetIds
     * @return multitype:
     */
    public function findAllIn(array $widgetIds)
    {
        $qb = $this->getAllIn($widgetIds);

        return $qb->getQuery()->getResult();
    }
}
