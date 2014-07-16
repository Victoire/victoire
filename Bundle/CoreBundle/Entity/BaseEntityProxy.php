<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\Widget;
use Behat\Behat\Exception\Exception;

/**
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseEntityProxy
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected  $id;

    /**
     *  Auto list mode: businessentity type
     * @var string
     * @ORM\Column(name="business_entity_name", type="string", nullable=true)
     *
     */
    protected $businessEntityName;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get businessEntity
     *
     * @return integer
     */
    public function getBusinessEntityName()
    {
        return $this->businessEntityName;
    }

    /**
     * Set businessEntityName
     *
     * @param String $businessEntityName The business entity name
     */
    public function setBusinessEntityName($businessEntityName)
    {
        $this->businessEntityName = $businessEntityName;
    }

    /**
     * Get the entity of the proxy
     *
     * @return Entity
     *
     * @throws Exception
     */
    public function getEntity()
    {
        $entityName = $this->getBusinessEntityName();

        //test the entity name
        if ($entityName === null || $entityName === '') {
            throw new \Exception('The businessEntityName is not defined for the entityProxy with the id:'.$this->getId());
        }

        $functionName = 'get'.ucfirst($entityName);

        $entity = call_user_func(array($this, $functionName));

        return $entity;
    }

    /**
     * Set the entity
     *
     * @param unknown $entity
     *
     * @throws \Exception
     */
    public function setEntity($entity)
    {
        $className = get_class($entity);

        //split
        $namespaceEntries = explode("\\", $className);

        $businessEntityName = array_pop($namespaceEntries);

        if ($businessEntityName === null) {
            throw new \Exception('No business entity name were found for the entity.');
        }

        $businessEntityName = strtolower($businessEntityName);

        //set the business entity name
        $this->setBusinessEntityName($businessEntityName);

        //set the entity
        $method = 'set'.ucfirst($businessEntityName);

        //set the entity
        call_user_func(array($this, $method), $entity);
    }
}
