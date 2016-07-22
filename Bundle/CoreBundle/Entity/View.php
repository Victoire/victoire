<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\TemplateBundle\Entity\Template;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

/**
 * Victoire View
 * A victoire view is a visual representation with a widget map.
 *
 * @Gedmo\Tree(type="nested")
 * @Gedmo\TranslationEntity(class="Victoire\Bundle\I18nBundle\Entity\ViewTranslation")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\CoreBundle\Repository\ViewRepository")
 * @ORM\Table("vic_view")
 * @ORM\HasLifecycleCallbacks
 */
abstract class View
{
    use \Gedmo\Timestampable\Traits\TimestampableEntity;
    use Translatable;

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
     * @var [WidgetMap]
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap", mappedBy="view", orphanRemoval=true, cascade={"persist", "remove"})
     */
    protected $widgetMaps = [];

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="View", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
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
     * @var ViewReference[]
     *                      The reference is related to viewsReferences.xml file which list all app views.
     *                      This is used to speed up the routing system and identify virtual pages (BusinessPage).
     */
    protected $references;

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
     * @deprecated
     * @ORM\Column(name="widget_map", type="array")
     */
    protected $widgetMap = [];

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\WidgetBundle\Entity\Widget", mappedBy="view", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $widgets;
    /**
     * @var bool
     *
     * @ORM\Column(name="cssUpToDate", type="boolean")
     */
    protected $cssUpToDate = false;

    /**
     * Construct.
     **/
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->widgetMaps = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->references = [];
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
     * @return string
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
     * @return int
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
        $this->children->removeElement($child);
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
     * Set widgets.
     *
     * @param [WidgetMap] $widgetMaps
     *
     * @return View
     */
    public function setWidgetMaps($widgetMaps)
    {
        $this->widgetMaps = $widgetMaps;

        return $this;
    }

    /**
     * Get widgets.
     *
     * @return Collection[WidgetMap]
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
        if (!$widgetMap->getView()) {
            $widgetMap->setView($this);
        }
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
     * Get widgets ids as array.
     *
     * @return array
     */
    public function getWidgetsIds()
    {
        $widgetIds = [];
        foreach ($this->getBuiltWidgetMap() as $slot => $_widgetMaps) {
            foreach ($_widgetMaps as $widgetMap) {
                foreach ($widgetMap->getWidgets() as $widget) {
                    $widgetIds[] = $widget->getId();
                }
            }
        }

        return $widgetIds;
    }

    /**
     * Get builtWidgetMap.
     *
     * @return array
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
     * Get reference according to the current locale.
     *
     * @param string $locale
     *
     * @return null|ViewReference
     */
    public function getReference($locale = null)
    {
        $locale = $locale ?: $this->getCurrentLocale();
        if (is_array($this->references) && isset($this->references[$locale])) {
            return $this->references[$locale];
        }
    }

    /**
     * Get references.
     *
     * @return ViewReference[]
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Set references.
     *
     * @param ViewReference[] $references
     *
     * @return $this
     */
    public function setReferences($references)
    {
        $this->references = $references;

        return $this;
    }

    /**
     * Set reference.
     *
     * @param ViewReference $reference
     * @param string        $locale
     *
     * @return $this
     */
    public function setReference(ViewReference $reference, $locale = null)
    {
        $locale = $locale ?: $this->getCurrentLocale();
        $this->references[$locale] = $reference;

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
     * @deprecated
     * Get widgetMap.
     *
     * @return widgetMap
     */
    public function getWidgetMap()
    {
        return $this->widgetMap;
    }

    /**
     * @deprecated
     * Get widgets.
     *
     * @return string
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    public function isTemplateOf(View $view)
    {
        while ($_view = $view->getTemplate()) {
            if ($this == $_view) {
                return true;
            }
            $view = $_view;
        }

        return false;
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

    /**
     * {@inheritdoc}
     */
    public static function getTranslationEntityClass()
    {
        return '\\Victoire\\Bundle\\I18nBundle\\Entity\\ViewTranslation';
    }

    public function getName()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getName');
    }

    public function setName($name, $locale = null)
    {
        $this->translate($locale, false)->setName($name);
        $this->mergeNewTranslations();
    }

    public function getSlug()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(null, false), 'getSlug');
    }

    public function setSlug($slug, $locale = null)
    {
        $this->translate($locale, false)->setSlug($slug);
        $this->mergeNewTranslations();
    }
}
