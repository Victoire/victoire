<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * The Entity proxy is the link between a view, a widget or any else with the BusinessEntity.
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseEntityProxy
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
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget", mappedBy="entityProxy")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $widgets;

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
     * @param string $entityId
     *
     * @throws \Exception
     *
     * @return object
     */
    public function getEntity($entityId)
    {
        //test the entity name
        if ($entityId == null) {
            throw new \Exception('The businessEntityId is not defined for the entityProxy with the id:'.$this->getId());
        }

        $functionName = 'get'.ucfirst($entityId);
        $entity = call_user_func([$this, $functionName]);

        return $entity;
    }

    /**
     * Set the entity.
     *
     * @param $entity
     * @param $entityId
     *
     * @throws \Exception
     */
    public function setEntity($entity, $entityId)
    {
        //set the entity
        $method = 'set'.ucfirst($entityId);

        //set the entity
        call_user_func([$this, $method], $entity);
    }

    /**
     * Set widgets.
     *
     * @param string $widgets
     *
     * @return BaseEntityProxy
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
     * Get widgets.
     *
     * @return string
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * Add widget.
     *
     * @param Widget $widget
     */
    public function addWidget(Widget $widget)
    {
        $this->widgets[] = $widget;
    }

    /**
     * Remove widget.
     *
     * @param Widget $widget
     */
    public function removeWidget(Widget $widget)
    {
        $this->widgets->remove($widget);
    }

    /**
     * has widget.
     *
     * @param Widget $widget
     *
     * @return bool
     */
    public function hasWidget(Widget $widget)
    {
        return $this->widgets->contains($widget);
    }
}
