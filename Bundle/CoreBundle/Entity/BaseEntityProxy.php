<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\Widget;

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
     * Method used to get the value of asked $name parameter in the entity $entityName if exists else in the related widget
     * @param string $entityName The related entity name
     * @param string $name       The property to get
     * @return mixed  A string, a relation or anything as value
     */
    public function getReferedValue($entityName, $name)
    {
        if ($this->getEntity($entityName) && $this->getWidget() && array_key_exists($name, $this->getWidget()->getFields()) ) {
            $fields = $this->getWidget()->getFields();

            return $this->getEntity($entityName)->{'get' . ucfirst($fields[$name])}();
        } else if ($this->getWidget() && method_exists($this->getWidget(), 'get'.ucfirst($name))) {
            return $this->getWidget()->{'get' . ucfirst($name)}();
        } else {
            throw new \Exception(sprintf('% Object doesn\'t have any property or relation named %s', get_class($this->getWidget()), 'get'.ucfirst($name)));
        }
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
     */
    public function getEntity()
    {
        $entityName = $this->getBusinessEntityName();

        //test the entity name
        if ($entityName === null || $entityName === '') {
            throw new \Exception('The businessEntityName is not defined for the entityProxy with the id:'.$this->getId());
        }

        return $this->{'get'.ucfirst($entityName)}();
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
        call_user_method($method, $this, $entity);
    }
}
