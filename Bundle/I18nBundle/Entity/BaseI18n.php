<?php

namespace Victoire\Bundle\I18nBundle\Entity;

use Behat\Behat\Exception\Exception;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseI18n
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the entity of the proxy.
     *
     * @throws Exception
     *
     * @return Entity
     */
    public function getEntity($entityName)
    {
        //test the entity name
        if ($entityName === null) {
            throw new \Exception('The businessEntityName is not defined for the entityProxy with the id:'.$this->getId());
        }

        $functionName = 'get'.ucfirst($entityName);
        $entity = call_user_func([$this, $functionName]);

        return $entity;
    }

    /**
     * Set the entity.
     *
     * @param unknown $entity
     *
     * @throws \Exception
     */
    public function setEntity($entity)
    {
        $className = get_class($entity);

        //split
        $namespaceEntries = explode('\\', $className);

        $businessEntityName = array_pop($namespaceEntries);

        if ($businessEntityName === null) {
            throw new \Exception('No business entity name were found for the entity.');
        }

        //set the entity
        $method = 'set'.ucfirst($businessEntityName);

        //set the entity
        call_user_func([$this, $method], $entity);
    }
}
