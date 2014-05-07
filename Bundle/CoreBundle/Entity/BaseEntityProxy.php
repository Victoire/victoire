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
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\Widget", mappedBy="entity")
     */
    protected $widget;

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
     * get related entity
     * @param string $entityName The related entity name
     * @return mixed
     */
    public function getEntity($entityName)
    {
        return $this->{'get'.ucfirst($entityName)}();
    }

    /**
     * Set widget
     *
     * @param Widget $widget
     * @return EntityProxy
     */
    public function setWidget(Widget $widget = null)
    {
        $this->widget = $widget;

        return $this;
    }

    /**
     * Get widget
     *
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

}
