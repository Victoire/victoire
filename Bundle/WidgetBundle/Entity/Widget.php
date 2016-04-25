<?php

namespace Victoire\Bundle\WidgetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Victoire\Bundle\CoreBundle\Entity\BaseEntityProxy;
use Victoire\Bundle\CriteriaBundle\Entity\Criteria;
use Victoire\Bundle\QueryBundle\Entity\QueryTrait;
use Victoire\Bundle\QueryBundle\Entity\VictoireQueryInterface;
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
class Widget extends BaseWidget implements VictoireQueryInterface
{
    use StyleTrait;
    use QueryTrait;
    use TimestampableEntity;

    public function __construct()
    {
        $this->childrenSlot = uniqid();
        $this->criterias = new ArrayCollection();
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
     * @var WidgetMap
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", inversedBy="widgets")
     * @ORM\JoinColumn(name="widget_map_id", referencedColumnName="id", onDelete="SET NULL"))
     */
    protected $widgetMap;

    /**
     * @var [Criteria]
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\CriteriaBundle\Entity\Criteria", mappedBy="widget", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $criterias;

    /**
     * @var string
     *
     * @ORM\Column(name="quantum", type="string", length=255, nullable=true)
     */
    protected $quantum;

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
     * @param BaseEntityProxy $entityProxy
     */
    public function setEntityProxy(BaseEntityProxy $entityProxy)
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
    public function setWidgetMap(WidgetMap $widgetMap)
    {
        $this->widgetMap = $widgetMap;
        $widgetMap->addWidget($this);

        return $this;
    }

    /**
     * Get widgets.
     *
     * @return [WidgetMap]
     */
    public function getWidgetMap()
    {
        return $this->widgetMap;
    }

    /**
     * Set the entity.
     *
     * @param object $entity
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
            if ($entityProxy !== null && $this->getBusinessEntityId()) {
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

    /**
     * @return [Criteria]
     */
    public function getCriterias()
    {
        return $this->criterias;
    }

    /**
     * @param [Criteria] $criterias
     */
    public function setCriterias($criterias)
    {
        $this->criterias = $criterias;
    }
    /**
     * @param Criteria $criteria
     */
    public function addCriteria($criteria)
    {
        $criteria->setWidget($this);
        $this->criterias[] = $criteria;
    }

    /**
     * @param Criteria $criteria
     */
    public function removeCriteria(Criteria $criteria)
    {
        $criteria->setWidget(null);
        $this->criterias->removeElement($criteria);
    }

    /**
     * @param Criteria $criteria
     *
     * @return bool
     */
    public function hasCriteria(Criteria $criteria)
    {
        return $this->criterias->contains($criteria);
    }

    /**
     * @param $criteriaAlias
     *
     * @return bool
     */
    public function hasCriteriaNamed($criteriaAlias)
    {
        return $this->criterias->exists(function($key, $element) use ($criteriaAlias) {
            return $criteriaAlias === $element->getName();
        });
    }

    /**
     * @return string
     */
    public function getQuantum()
    {
        return $this->quantum;
    }

    /**
     * @param string $quantum
     */
    public function setQuantum($quantum)
    {
        $this->quantum = $quantum;
    }

}
