<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\I18nBundle\Entity\I18n;

/**
 * Victoire View
 * A victoire view is a visual representation with a widget map
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\Entity
 * @ORM\Table("vic_view")
 * @ORM\HasLifecycleCallbacks
 */
abstract class View
{
    use \Gedmo\Timestampable\Traits\TimestampableEntity;

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
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="bodyId", type="string", length=255, nullable=true)
     */
    protected $bodyId;

    /**
     * @var string
     *
     * @ORM\Column(name="bodyClass", type="string", length=255, nullable=true)
     */
    protected $bodyClass;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, updatable=false, unique=false)
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget", mappedBy="view")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $widgets;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * Could be Template or BusinessEntityPagePattern
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\TemplateBundle\Entity\Template", inversedBy="inheritors", cascade={"persist"})
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="CASCADE")
     *
     */
    protected $template;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\TemplateBundle\Entity\Template", mappedBy="template")
     */
    protected $inheritors;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\PageBundle\Entity\BasePage", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    protected $position = 0;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\PageBundle\Entity\BasePage", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
    * This relation is dynamicly added by PageSubscriber
    */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(name="undeletable", type="boolean")
     */
    protected $undeletable = false;

    /**
     * @ORM\Column(name="widget_map", type="array")
     */
    protected $widgetMap = array();

    protected $builtWidgetMap = array();

    //the slot contains the widget maps entities
    protected $slots = array();

    //The reference is related to viewsReferences.xml fil which list all app views.
    //This is used to speed up the routing system and to identify virtual pages (BusinessEntityPage)
    protected $reference;

    /**
     * @ORM\Column(name="locale", type="string")
     */
    protected $locale;

    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\I18nBundle\Entity\I18n", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $i18n;

    /**
     * contruct
     **/
    public function __construct()
    {
        $this->widgets = new ArrayCollection();
        $this->widgetMap = array();
    }

    /**
     * to string
     *
     * @return string
     **/
    public function __toString()
    {
        return $this->getName();
    }

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
     * Set id
     * @param id $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string $name
     *
     * @return View
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set slug
     * @param string $slug
     *
     * @return View
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set template
     * @param Page $template
     *
     * @return View
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set page
     * @param string $inheritors
     *
     * @return Template
     */
    public function setInheritors($inheritors)
    {
        $this->inheritors = $inheritors;

        return $this;
    }

    /**
     * Get inheritors (all Templates having this object as Template)
     *
     * @return string
     */
    public function getInheritors()
    {
        return $this->inheritors;
    }

    /**
     * Set parent
     *
     * @param \Victoire\Bundle\PageBundle\Entity\BasePage $parent
     */
    public function setParent(\Victoire\Bundle\PageBundle\Entity\BasePage $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return \Victoire\Bundle\PageBundle\Entity\BasePage parent
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * Set children
     * @param string $children
     *
     * @return Page
     */
    public function setChildren($children)
    {
        $this->children = $children;
        if($children !== null) {
           foreach ($children as $child) {
                $child->setParent($this);
            }
        }

        return $this;
    }

    /**
     * Get children
     *
     * @return string
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add child
     *
     * @param child $child
     */
    public function addChild(View $child)
    {
        $this->children[] = $child;
    }

    /**
     * Remove child
     *
     * @param child $child
     */
    public function removeChild(View $child)
    {
        $this->children->remove($child);
    }

    /**
     * Get the left value
     *
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set the left value
     *
     * @param integer $lft
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    /**
     * Get the right value
     *
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set the right value
     *
     * @param integer $rgt
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }

    /**
     * Get the level value
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set the level value
     *
     * @param integer $lvl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    }

    /**
     * Get the root value
     *
     * @return integer
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set the root value
     *
     * @param integer $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * Set undeletable
     *
     * @param boolean $undeletable
     *
     * @return View The current instance
     *
     */
    public function setUndeletable($undeletable)
    {
        $this->undeletable = $undeletable;

        return $this;
    }

    /**
     * Is the widget is undeletable
     *
     * @return boolean
     */
    public function isUndeletable()
    {
        return $this->undeletable;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get bodyId
     *
     * @return string
     */
    public function getBodyId()
    {
        return $this->bodyId;
    }

    /**
     * Set bodyId
     * @param string $bodyId
     *
     * @return $this
     */
    public function setBodyId($bodyId)
    {
        $this->bodyId = $bodyId;

        return $this;
    }

    /**
     * Get bodyClass
     *
     * @return string
     */
    public function getBodyClass()
    {
        return $this->bodyClass;
    }

    /**
     * Set bodyClass
     * @param string $bodyClass
     *
     * @return $this
     */
    public function setBodyClass($bodyClass)
    {
        $this->bodyClass = $bodyClass;

        return $this;
    }

    /**
     * Initialize I18n table
     * @ORM\PrePersist
     */
    public function initI18n()
    {
        if (!$this->i18n) {
            $this->i18n = new I18n();
            $this->i18n->setTranslation($this->getLocale(), $this);
        }
    }

    /**
     * Get i18n
     *
     * @return string
     */
    public function getI18n()
    {
        return $this->i18n;
    }

    /**
     * Set i18n
     * @param string $i18n
     *
     * @return $this
     */
    public function setI18n(I18n $i18n)
    {
        $this->i18n = $i18n;
        $this->i18n->setTranslation($this->getLocale(), $this);

        return $this;
    }

    /**
     * Set widgets
     * @param string $widgets
     *
     * @return View
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
     * Get widgets
     * @param string $slot
     *
     * @return string
     */
    public function getWidgetsForSlot($slot)
    {
        $widgets = array();
        foreach ($this->getWidgets() as $widget) {
            if ($widget->getSlot() === $slot) {
                $widgets[] = $widget;
            }
        }

        return $widgets;
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

    /**
     * Set widgetMap
     *
     * @param widgetMap $widgetMap
     */
    public function setWidgetMap($widgetMap)
    {
        $this->widgetMap = $widgetMap;
    }

    /**
     * Get widgetMap
     *
     * @return widgetMap
     */
    public function getWidgetMap($built = true)
    {
        if ($built) {
            return $this->builtWidgetMap;
        }

        return $this->widgetMap;
    }

    /**
     * Method called once the entity is loaded
     *
     * @ORM\PostLoad
     */
    public function postLoad()
    {
        $widgetMap = $this->widgetMap;

        //the slots of the page
        $slots = array();

        //convert the widget map array as objects
        foreach ($widgetMap as $slotId => $_widgetMapEntries) {
            $slot = new Slot();
            $slot->setId($slotId);

            foreach ($_widgetMapEntries as $_widgetMapEntry) {
                $_widgetMap = new WidgetMap();
                $_widgetMap->setAction($_widgetMapEntry['action']);
                $_widgetMap->setPosition($_widgetMapEntry['position']);
                $_widgetMap->setPositionReference($_widgetMapEntry['positionReference']);
                $_widgetMap->setReplacedWidgetId($_widgetMapEntry['replacedWidgetId']);
                $_widgetMap->setWidgetId(intval($_widgetMapEntry['widgetId']));

                $slot->addWidgetMap($_widgetMap);
            }

            $slots[] = $slot;
        }

        //set the slots to the page
        $this->slots = $slots;
    }

    /**
     * Method before updating a page
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        //we update the widget map by the slots
        if (!empty($this->slots)) {
            $this->updateWidgetMapBySlots();
        }
    }

    /**
     * Set the slots
     * @param unknown $slots
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;

        //convert the slots object in a widget map array
        $this->updateWidgetMapBySlots();
    }

    public function computeCompleteSlot($slotId)
    {
        $slot = $this->getSlotById($slotId);
        if (null !== $template = $this->getTemplate()) {
            // Is the parent has the slot in it's widgetMaps ?
            if (null !== $templateSlot = $template->computeCompleteSlot($slotId)) {
                foreach ($templateSlot->getWidgetMaps() as $widgetMap) {
                    //
                    $widgetMap->setAction(WidgetMap::ACTION_OVERWRITE);
                    $widgetMap->setReplacedWidgetId($widgetMap->getWidgetId());
                    $slot->addWidgetMap($widgetMap);
                }
            }
        }
        $this->slots[$slotId] = $slot;

        return $slot;

    }
    /**
     * Convert slots to a widget map
     *
     * @return array The widget map
     */
    protected function convertSlotsToWidgetMap()
    {
        $slots = $this->slots;

        $widgetMap = array();

        //parse the slots
        foreach ($slots as $slot) {
            $slotId = $slot->getId();
            $widgetMap[$slotId] = array();

            $widgetMaps = $slot->getWidgetMaps();

            //parse the widget map objects
            foreach ($widgetMaps as $_widgetMap) {
                $widgetMapEntry = array();
                $widgetMapEntry['action'] = $_widgetMap->getAction();
                $widgetMapEntry['position'] = $_widgetMap->getPosition();
                $widgetMapEntry['positionReference'] = $_widgetMap->getPositionReference();
                $widgetMapEntry['replacedWidgetId'] = $_widgetMap->getReplacedWidgetId();
                $widgetMapEntry['widgetId'] = $_widgetMap->getWidgetId();

                //add the temp slot to the widget map
                $widgetMap[$slotId][] = $widgetMapEntry;
            }
        }

        return $widgetMap;
    }

    /**
     * This function update the widgetMap array using the slots entities array
     *
     */
    public function updateWidgetMapBySlots()
    {
        //generate widget map by the slots
        $widgetMap = $this->convertSlotsToWidgetMap();

        //update widget map
        $this->setWidgetMap($widgetMap);
    }

    /**
     * Get the slot by the slotId
     *
     * @param string $slotId
     *
     * @return Slot
     */
    public function getSlotById($slotId)
    {
        $slot = null;

        $slots = $this->slots;

        //parse all slots
        foreach ($slots as $sl) {
            //if this the slot we are looikong for
            if ($sl->getId() === $slotId) {
                $slot = $sl;
                //there no need to continue, we found the slot
                break;
            }
        }

        return $slot;
    }
    /**
     * Update the given slot
     *
     * @param Slot $slot
     *
     * @return this
     */
    public function updateSlot($slot)
    {
        $slot = null;

        $slots = $this->slots;

        //parse all slots
        foreach ($slots as $key => $_slot) {
            //if this the slot we are looking for
            if ($_slot->getId() === $slot->getId()) {
                $this->slots[$key] = $slot;
                //there no need to continue, we found the slot
                break;
            }
        }

        return $this;
    }

    /**
     * Add a slot to the slots array
     *
     * @param Slot $slot The slot to add
     */
    public function addSlot(Slot $slot)
    {
        $this->slots[] = $slot;
    }

    /**
     * Remove slots
     * @param Widget $slots
     */
    public function removeSlot(Slot $slots)
    {
        $this->slots->remove($slots);
    }

    /**
     * Get the slots
     *
     * @return array The slots
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Get discriminator type
     *
     * @return integer
     */
    public function getType()
    {
        $class = get_called_class();

        return $class::TYPE;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set reference
     * @param string $reference
     *
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get builtWidgetMap
     *
     * @return string
     */
    public function getBuiltWidgetMap()
    {
        return $this->builtWidgetMap;
    }

    /**
     * Set builtWidgetMap
     *
     * @param  string $builtWidgetMap
     * @return $this
     */
    public function setBuiltWidgetMap($builtWidgetMap)
    {
        $this->builtWidgetMap = $builtWidgetMap;

        return $this;
    }
}
