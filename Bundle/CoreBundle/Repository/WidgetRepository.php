<?php
namespace Victoire\Bundle\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Victoire\Bundle\PageBundle\Entity\BasePage;

class WidgetRepository extends EntityRepository
{
    public function getAllIn(array $widgetIds)
    {
        return $this->createQueryBuilder('widget')
            ->where('widget.id IN (:map)')
            ->setParameter('map', $widgetIds);
    }

    public function findAllIn(array $widgetIds)
    {
        $qb = $this->getAllIn($widgetIds);

        return $qb->getQuery()->getResult();

    }

}
