<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\WidgetBundle\Model\Widget;

/**
 * The Entity proxy is the link between a view, a widget or any else with the BusinessEntity
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
    protected $id;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget", mappedBy="entityProxy")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $widgets;

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
     * Get the entity of the proxy
     *
     * @return Object
     * @throws \Exception
     */
    public function getEntity($entityName)
    {
        //test the entity name
        if ($entityName == null) {
            throw new \Exception('The businessEntityName is not defined for the entityProxy with the id:'.$this->getId());
        }

        $functionName = 'get'.ucfirst($entityName);
        $entity = call_user_func(array($this, $functionName));

        return $entity;
    }

    /**
     * Set the entity
     * @param $entity
     * @param $entityName
     *
     * @throws \Exception
     */
    public function setEntity($entity, $entityName)
    {
        //set the entity
        $method = 'set'.ucfirst($entityName);

        //set the entity
        call_user_func(array($this, $method), $entity);
    }

    /**
     * Set widgets
     * @param string $widgets
     *
     * @return EntityProxy
     */
    public function setWidgets($widgets)
    {
        $this->widgets = $widgets;

        foreach ($widgets as $widget) {
            $widget->setView($this);
        }

        return $this;
    }

    /**
     * Get widgets
     *
     * @return string
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * Add widget
     * @param Widget $widget
     */
    public function addWidget(Widget $widget)
    {
        $this->widgets[] = $widget;
    }

    /**
     * Remove widget
     * @param Widget $widget
     */
    public function removeWidget(Widget $widget)
    {
        $this->widgets->remove($widget);
    }

    /**
     * has widget
     * @param Widget $widget
     *
     * @return bool
     */
    public function hasWidget(Widget $widget)
    {
        return $this->widgets->contains($widget);
    }
}
