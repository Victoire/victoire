<?php

namespace Victoire\Bundle\TemplateBundle\Repository;

use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * The Page repository.
 */
class TemplateRepository extends NestedTreeRepository
{
    private $qb;

    /**
     * Get query builder instance.
     */
    public function getInstance()
    {
        return $this->qb ? $this->qb : $this->createQueryBuilder('template');
    }

    /**
     * Get all templates in the repository.
     *
     * @return TemplateRepository
     */
    public function getAll()
    {
        return $this;
    }

    public function getPatterns()
    {
        $this->qb = $this->_em->createQueryBuilder()
            ->select('pattern')
            ->from('Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate', 'pattern');

        return $this;
    }

    /**
     * Run query builder instance.
     *
     * @param method        $method        The method to run
     * @param hydrationMode $hydrationMode How the results will be (Object ? Array )
     *
     * @return array()
     */
    public function run($method = 'getResult', $hydrationMode = Query::HYDRATE_OBJECT)
    {
        return $this->getInstance()->getQuery()->$method($hydrationMode);
    }
}
