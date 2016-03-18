<?php
/**
 * Created by PhpStorm.
 * User: paulandrieux
 * Date: 17/03/2016
 * Time: 18:35
 */

namespace Victoire\Bundle\CriteriaBundle\Criteria;



class BaseCriteria
{
    private $object;

    public function __call($name, $args = null)
    {
        $this->object->{$name}($args);

    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }


}
