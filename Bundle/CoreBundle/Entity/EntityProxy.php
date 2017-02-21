<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * The Entity proxy is the link between a view, a widget or any else with the BusinessEntity.
 *
 * @ORM\Table("vic_entity_proxy")
 * @ORM\Entity()
 */
class EntityProxy
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
     * id of the ressource (could be an integer, an hash...).
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $ressourceId;

    /**
     * @var BusinessEntity
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity")
     * @ORM\JoinColumn(name="business_entity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $businessEntity;
    protected $entity;

    /**
     * @var array
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $additionnalProperties;

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
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the entity of the proxy.
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the entity.
     *
     * @param $entity
     * @param $entityId
     *
     * @throws \Exception
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Set widgets.
     *
     * @param array $widgets
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

    /**
     * @return BusinessEntity
     */
    public function getBusinessEntity()
    {
        return $this->businessEntity;
    }

    /**
     * @param BusinessEntity $businessEntity
     */
    public function setBusinessEntity($businessEntity)
    {
        $this->businessEntity = $businessEntity;
    }

    /**
     * @return mixed
     */
    public function getRessourceId()
    {
        return $this->ressourceId;
    }

    /**
     * @param mixed $ressource
     */
    public function setRessourceId($ressourceId)
    {
        $this->ressourceId = $ressourceId;
    }

    /**
     * @return array
     */
    public function getAdditionnalProperties()
    {
        return unserialize($this->additionnalProperties);
    }

    /**
     * @param array $additionnalProperties
     */
    public function setAdditionnalProperties($additionnalProperties)
    {
        $this->additionnalProperties = serialize($additionnalProperties);
    }
}
