<?php

namespace Victoire\Bundle\WidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\QueryBundle\Entity\Traits\QueryTrait;
use Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTrait;
use Victoire\Bundle\WidgetBundle\Model\Widget as BaseWidget;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

/**
 * Widget.
 *
 * @ORM\Table("vic_widget")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\WidgetBundle\Repository\WidgetRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
class Widget extends BaseWidget
{
    use StyleTrait;
    use QueryTrait;

    public function __construct()
    {
        $this->childrenSlot = uniqid();
    }

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
     * @ORM\Column(name="slot", type="string", length=255, nullable=true)
     */
    protected $slot;

    /**
     * @var string
     *
     * @ORM\Column(name="childrenSlot", type="string", length=100, nullable=true)
     */
    protected $childrenSlot;

    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string", length=255, nullable=true)
     */
    protected $theme;

    /**
     * @var string
     *
     * @ORM\Column(name="asynchronous", type="boolean", nullable=true)
     */
    protected $asynchronous;

    /**
     * @var string
     *
     * @ORM\Column(name="fields", type="array")
     */
    protected $fields = [];

    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=255, nullable=false)
     */
    protected $mode = self::MODE_STATIC;

    /**
     * Auto simple mode: joined entity.
     *
     * @var EntityProxy
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\EntityProxy", inversedBy="widgets", cascade={"persist"})
     * @ORM\JoinColumn(name="entityProxy_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $entityProxy;

    /**
     * The entity linked to the widget.
     *
     * @var unknown
     */
    protected $entity;

    /**
     * @deprecated
     *
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\View", inversedBy="widgets", cascade={"persist"})
     * @ORM\JoinColumn(name="view_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $view;
    /**
     * @deprecated
     *
     * @var [WidgetMap]
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", mappedBy="widget", orphanRemoval=true, cascade={"persist", "remove"})
     */
    protected $widgetMaps;

    /**
     * @return string
     */
    public function isAsynchronous()
    {
        return $this->asynchronous;
    }

    /**
     * @param string $asynchronous
     */
    public function setAsynchronous($asynchronous)
    {
        $this->asynchronous = $asynchronous;
    }

    /**
     * Set the entity proxy.
     *
     * @param EntityProxy $entityProxy
     */
    public function setEntityProxy(EntityProxy $entityProxy)
    {
        $this->entityProxy = $entityProxy;
    }

    /**
     * Get the entity proxy.
     *
     * @return EntityProxy
     */
    public function getEntityProxy()
    {
        return $this->entityProxy;
    }

    /**
     * to string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }

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
     * Set fields.
     *
     * @param string $fields
     *
     * @return Widget
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get fields.
     *
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set The Id.
     *
     * @param int $id The id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set slot.
     *
     * @param string $slot
     *
     * @return Widget
     */
    public function setSlot($slot)
    {
        $this->slot = $slot;

        return $this;
    }

    /**
     * Get slot.
     *
     * @return string
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * Set theme.
     *
     * @param string $theme
     *
     * @return Widget
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme.
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Get the content.
     *
     * @return unknown
     */
    public function getValue()
    {
        //return $this->getContent();
        return;
    }

    /**
     * Get the type of the object.
     *
     * @return string The type
     */
    public function getType()
    {
        return $this->guessType();
    }

    /**
     * Guess the type of this by exploding and getting the last item.
     *
     * @return string The guessed type
     */
    protected function guessType()
    {
        $type = explode('\\', get_class($this));

        return strtolower(preg_replace('/Widget/', '', end($type)));
    }

    /**
     * Set the mode.
     *
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Get the mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get the view id.
     *
     * @return int The view id
     */
    public function getViewId()
    {
        $viewId = null;

        $view = $this->getView();

        if ($view !== null) {
            $viewId = $view->getId();
        }

        return $viewId;
    }

    /**
     * @return string
     */
    public function getChildrenSlot()
    {
        return $this->childrenSlot ?: $this->getId();
    }

    /**
     * @param string $childrenSlot
     */
    public function setChildrenSlot($childrenSlot)
    {
        $this->childrenSlot = $childrenSlot;
    }

    /**
     * Set widgets.
     *
     * @param [WidgetMap] $widgetMaps
     *
     * @return Widget
     */
    public function setWidgetMaps($widgetMaps)
    {
        $this->widgetMaps = $widgetMaps;

        foreach ($widgetMaps as $widgetMap) {
            $widgetMap->setWidget($this);
        }

        return $this;
    }

    /**
     * Get widgets.
     *
     * @return [WidgetMap]
     */
    public function getWidgetMaps()
    {
        return $this->widgetMaps;
    }

    /**
     * Add widget.
     *
     * @param Widget $widgetMap
     */
    public function addWidgetMap(WidgetMap $widgetMap)
    {
        $widgetMap->setWidget($this);
        $this->widgetMaps[] = $widgetMap;
    }

    /**
     * Remove a widgetMap.
     *
     * @param WidgetMap $widgetMap
     */
    public function removeWidgetMap(WidgetMap $widgetMap)
    {
        $this->widgetMaps->removeElement($widgetMap);
    }

    /**
     * Set the entity.
     *
     * @param unknown $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get the entity.
     *
     * @return number
     */
    public function getEntity()
    {
        //if there is no entity
        if ($this->entity === null) {
            //we try to get one from the proxy
            $entityProxy = $this->getEntityProxy();

            //if there is a proxy
            if ($entityProxy !== null) {
                $entity = $entityProxy->getEntity($this->getBusinessEntityId());
                $this->entity = $entity;
            }
        }

        return $this->entity;
    }

    /**
     * Clone a widget.
     */
    public function __clone()
    {
        // if there is a proxy
        if ($this->entityProxy) {
            // we clone this one
            $this->entityProxy = clone $this->entityProxy;
        }

        // This check should be in the __constructor, but Doctrine does not use __constructor to
        // instanciate entites but __clone method.
        if (property_exists(get_called_class(), 'widget')) {
            throw new \Exception(sprintf('A property $widget was found in %s object.
                The $widget property is reserved for Victoire.
                You should chose a different property name.', get_called_class()));
        }
    }

    /**
     * @deprecated
     * Get view.
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }
}
