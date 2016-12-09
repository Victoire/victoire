<?php

namespace Victoire\Bundle\ORMBusinessEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 * The ORM business Entity.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\ORMBusinessEntityBundle\Entity\ORMBusinessEntityRepository")
 */
class ORMBusinessEntity extends BusinessEntity
{
    const TYPE = 'orm';

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=255, nullable=true)
     */
    protected $class = null;

    /**
     * Get the class.
     *
     * @return string The class
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the class.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }
    public function getType()
    {
        return self::TYPE;
    }
}
