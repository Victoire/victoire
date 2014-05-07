<?php
namespace Victoire\Bundle\PageBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Victoire\Bundle\PageBundle\Entity\BasePage;

class BasePageRepository extends NestedTreeRepository
{
    public function getOneByUrl($url)
    {
        return $this->createQueryBuilder('page')
            // ->join('page.url', 'route')
            ->where('page.url = (:url)')
            ->setMaxResults(1)
            ->setParameter('url', $url);
    }
    public function findOneByUrl($url)
    {
        $qb = $this->getOneByUrl($url);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
