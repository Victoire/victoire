<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\I18nBundle\Entity\BaseI18n;
use Victoire\Bundle\I18nBundle\Entity\I18n;
use Victoire\Bundle\PageBundle\Entity\Slot;
use Victoire\Bundle\PageBundle\Entity\WidgetMap;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * Victoire View
 * A victoire view is a visual representation with a widget map.
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\CoreBundle\Repository\ViewRepository")
 * @ORM\Table("vic_view")
 * @ORM\HasLifecycleCallbacks
 */
abstract class View
{
    use \Gedmo\Timestampable\Traits\TimestampableEntity;

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
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Groups({"search"})
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
     * @Gedmo\Slug(handlers={
     *     @Gedmo\SlugHandler(class="Victoire\Bundle\BusinessEntityBundle\Handler\TwigSlugHandler"
     * )},fields={"name"}, updatable=false, unique=false)
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget", mappedBy="view", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $widgets;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="View", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
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
     * @ORM\OneToMany(targetEntity="View", mappedBy="parent", cascade={"remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children = [];

    /**
     * This relation is dynamicly added by PageSubscriber.
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
    protected $widgetMap = [];

    protected $builtWidgetMap = [];

    //the slot contains the widget maps entities
    protected $slots = [];

    //The reference is related to viewsReferences.xml file which list all app views.
    //This is used to speed up the routing system and identify virtual pages (BusinessPage)
    protected $reference;

    /**
     * @ORM\Column(name="locale", type="string")
     * @Serializer\Groups({"search"})
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
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\TemplateBundle\Entity\Template", inversedBy="inheritors", cascade={"persist"})
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $template;

    /**
     * @var string
     *
     * @ORM\Column(name="cssHash", type="string", length=40 ,nullable=true)
     */
    protected $cssHash;

    /**
     * @var bool
     *
     * @ORM\Column(name="cssUpToDate", type="boolean", options={"default"=0})
     */
    protected $cssUpToDate;

    /**
     * Construct.
     **/
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->widgets = new ArrayCollection();
        $this->widgetMap = [];
    }

    /**
     * to string.
     *
     * @return string
     **/
    public function __toString()
    {
        return $this->getName();
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
     * Set id.
     *
     * @param id $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale.
     *
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
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
     * Set slug.
     *
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
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set template.
     *
     * @param View $template
     *
     * @return View
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template.
     *
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set parent.
     *
     * @param View $parent
     */
    public function setParent(View $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent.
     *
     * @return View parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set children.
     *
     * @param View[] $children
     *
     * @return View
     */
    public function setChildren($children)
    {
        $this->children = $children;
        if ($children !== null) {
            foreach ($children as $child) {
                $child->setParent($this);
            }
        }

        return $this;
    }

    /**
     * Get children.
     *
     * @return View[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->children);
    }

    /**
     * Get WebView children.
     *
     * @return string
     */
    public function getWebViewChildren()
    {
        $webViewChildren = [];
        foreach ($this->children as $child) {
            if (!$child instanceof BusinessTemplate) {
                $webViewChildren[] = $child;
            }
        }

        return $webViewChildren;
    }

    /**
     * Add child.
     *
     * @param View $child
     */
    public function addChild(View $child)
    {
        $this->children[] = $child;
    }

    /**
     * Remove child.
     *
     * @param View $child
     */
    public function removeChild(View $child)
    {
        $this->children->remove($child);
    }

    /**
     * Get the left value.
     *
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set the left value.
     *
     * @param int $lft
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    /**
     * Get the right value.
     *
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set the right value.
     *
     * @param int $rgt
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }

    /**
     * Get the level value.
     *
     * @return int
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set the level value.
     *
     * @param int $lvl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    }

    /**
     * Get the root value.
     *
     * @return int
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set the root value.
     *
     * @param int $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * Set undeletable.
     *
     * @param bool $undeletable
     *
     * @return View The current instance
     */
    public function setUndeletable($undeletable)
    {
        $this->undeletable = $undeletable;

        return $this;
    }

    /**
     * Is the widget is undeletable.
     *
     * @return string
     */
    public function isUndeletable()
    {
        return $this->undeletable;
    }

    /**
     * Get author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set author.
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
     * Get bodyId.
     *
     * @return string
     */
    public function getBodyId()
    {
        return $this->bodyId;
    }

    /**
     * Set bodyId.
     *
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
     * Get bodyClass.
     *
     * @return string
     */
    public function getBodyClass()
    {
        return $this->bodyClass;
    }

    /**
     * Set bodyClass.
     *
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
     * Initialize I18n table.
     *
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
     * Get i18n.
     *
     * @return BaseI18n
     */
    public function getI18n()
    {
        return $this->i18n;
    }

    /**
     * Set i18n.
     *
     * @param string $i18n
     *
     * @return $this
     */
    public function setI18n(BaseI18n $i18n)
    {
        $this->i18n = $i18n;
        $this->i18n->setTranslation($this->getLocale(), $this);

        return $this;
    }

    /**
     * Set widgets.
     *
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
     * Get widgets.
     *
     * @return Widget[]
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * Get widgets ids as array.
     *
     * @return array
     */
    public function getWidgetsIds()
    {
        $widgetIds = [];

        $extractWidgetIds = function ($widgetMap) {
            /* @var $widgetMap WidgetMap */
            return $widgetMap->getWidgetId();
        };

        foreach ($this->getWidgetMap() as $widgetMapArray) {
            $widgetIds = array_merge(array_map($extractWidgetIds, $widgetMapArray), $widgetIds);
        }

        return $widgetIds;
    }

    /**
     * Get widgets.
     *
     * @param string $slot
     *
     * @return string
     */
    public function getWidgetsForSlot($slot)
    {
        $widgets = [];
        foreach ($this->getWidgets() as $widget) {
            if ($widget->getSlot() === $slot) {
                $widgets[] = $widget;
            }
        }

        return $widgets;
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
     * Set widgetMap.
     *
     * @param widgetMap $widgetMap
     */
    public function setWidgetMap($widgetMap)
    {
        $this->widgetMap = $widgetMap;
    }

    /**
     * Get widgetMap.
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
     * Method called once the entity is loaded.
     *
     * @ORM\PostLoad
     */
    public function postLoad()
    {
        $widgetMap = $this->widgetMap;

        //the slots of the page
        $slots = [];

        //convert the widget map array as objects
        foreach ($widgetMap as $slotId => $_widgetMapEntries) {
            $slot = new Slot();
            $slot->setId($slotId);

            foreach ($_widgetMapEntries as $_widgetMapEntry) {
                $_widgetMap = new WidgetMap();
                $_widgetMap->setAction(@$_widgetMapEntry['action']);
                $_widgetMap->setPosition(@$_widgetMapEntry['position']);
                $_widgetMap->setPositionReference(@$_widgetMapEntry['positionReference']);
                $_widgetMap->setAsynchronous(isset($_widgetMapEntry['asynchronous']) ? $_widgetMapEntry['asynchronous'] : null);
                $_widgetMap->setReplacedWidgetId(@$_widgetMapEntry['replacedWidgetId']);
                $_widgetMap->setWidgetId(intval($_widgetMapEntry['widgetId']));

                $slot->addWidgetMap($_widgetMap);
            }

            $slots[] = $slot;
        }

        //set the slots to the page
        $this->slots = $slots;
    }

    /**
     * Method before updating a page.
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
     * Set the slots.
     *
     * @param unknown $slots
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;

        //convert the slots object in a widget map array
        $this->updateWidgetMapBySlots();
    }

    /**
     * Convert slots to a widget map.
     *
     * @return array The widget map
     */
    protected function convertSlotsToWidgetMap()
    {
        $slots = $this->getSlots();

        $widgetMap = [];

        //parse the slots
        foreach ($slots as $slot) {
            $slotId = $slot->getId();
            $widgetMap[$slotId] = [];

            $widgetMaps = $slot->getWidgetMaps();

            //parse the widget map objects
            foreach ($widgetMaps as $_widgetMap) {
                $widgetMapEntry = [];
                $widgetMapEntry['action'] = $_widgetMap->getAction();
                $widgetMapEntry['position'] = $_widgetMap->getPosition();
                $widgetMapEntry['asynchronous'] = $_widgetMap->isAsynchronous();
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
     * This function update the widgetMap array using the slots entities array.
     */
    public function updateWidgetMapBySlots()
    {
        //generate widget map by the slots
        $widgetMap = $this->convertSlotsToWidgetMap();

        //update widget map
        $this->setWidgetMap($widgetMap);
    }

    /**
     * Get the slot by the slotId.
     *
     * @param string $slotId
     *
     * @return Slot
     */
    public function getSlotById($slotId)
    {
        foreach ($this->slots as $slot) {
            if ($slot->getId() === $slotId) {
                return $slot;
            }
        }
    }

    /**
     * Update the given slot.
     *
     * @param Slot $slot
     *
     * @return View
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
     * Add a slot to the slots array.
     *
     * @param Slot $slot The slot to add
     */
    public function addSlot(Slot $slot)
    {
        $this->slots[] = $slot;
    }

    /**
     * Remove slots.
     *
     * @param Slot $slots
     */
    public function removeSlot(Slot $slots)
    {
        $this->slots->remove($slots);
    }

    /**
     * Get the slots.
     *
     * @return Slot[] The slots
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Get discriminator type.
     *
     * @return int
     */
    public function getType()
    {
        $class = get_called_class();

        return $class::TYPE;
    }

    /**
     * Set position.
     *
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get reference.
     *
     * @return ViewReference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set reference.
     *
     * @param ViewReference $reference
     *
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get builtWidgetMap.
     *
     * @return string
     */
    public function getBuiltWidgetMap()
    {
        return $this->builtWidgetMap;
    }

    /**
     * Set builtWidgetMap.
     *
     * @param string $builtWidgetMap
     *
     * @return $this
     */
    public function setBuiltWidgetMap($builtWidgetMap)
    {
        $this->builtWidgetMap = $builtWidgetMap;

        return $this;
    }

    /**
     * Get CSS hash.
     *
     * @return string
     */
    public function getCssHash()
    {
        return $this->cssHash;
    }

    /**
     * Set CSS hash.
     *
     * @param string $cssHash
     *
     * @return $this
     */
    public function setCssHash($cssHash)
    {
        $this->cssHash = $cssHash;

        return $this;
    }

    /**
     * Change cssHash.
     */
    public function changeCssHash()
    {
        $this->cssHash = sha1(uniqid());
    }

    /**
     * Get cssUpToDate.
     *
     * @return bool
     */
    public function isCssUpToDate()
    {
        return $this->cssUpToDate;
    }

    /**
     * Set CssUpToDate.
     *
     * @param bool $cssUpToDate
     *
     * @return $this
     */
    public function setCssUpToDate($cssUpToDate)
    {
        $this->cssUpToDate = $cssUpToDate;

        return $this;
    }
}
