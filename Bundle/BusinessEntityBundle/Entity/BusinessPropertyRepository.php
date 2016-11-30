<?php

namespace Victoire\Bundle\BusinessEntityBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\Expr;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * The BusinessProperty Repository.
 */
class BusinessPropertyRepository extends EntityRepository
{
    use StateFullRepositoryTrait;
    protected $mainAlias = 'businessproperty';

    /**
     * @param array $classname
     */
    public function getByClassname($classname)
    {
        $this->getInstance()
            ->join('Victoire\Bundle\ORMBusinessEntityBundle\Entity\ORMBusinessEntity', 'be', Expr\Join::WITH, 'businessproperty.businessEntity = be.id')
            ->where('be.class = :classname')
            ->setParameter(':classname', $classname);

        return $this;
    }
}
