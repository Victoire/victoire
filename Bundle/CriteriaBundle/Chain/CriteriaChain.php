<?php

namespace Victoire\Bundle\CriteriaBundle\Chain;

use Doctrine\Common\Collections\Criteria;


/**
 * Criteria chain.
 */
class CriteriaChain
{
    private $criterias;

    public function __construct()
    {
        $this->criterias = [];
    }

    /**
     * @param $criteria
     * @param $alias
     */
    public function addCriteria($criteria, $alias)
    {
        $this->criterias[$alias] = $criteria;
    }

    /**
     * @param string $alias
     *
     * @return Criteria
     */
    public function getCriteria($alias)
    {
        if (array_key_exists($alias, $this->criterias)) {
            return $this->criterias[$alias];
        }

        return $this->criterias['default'];
    }

    /**
     * @return array
     */
    public function getCriterias()
    {
        return $this->criterias;
    }
}
