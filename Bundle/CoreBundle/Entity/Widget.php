<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Widget
 *
 * @ORM\Table("cms_widget")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\CoreBundle\Repository\WidgetRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
class Widget
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
     * @ORM\Column(name="slot", type="string", length=255, nullable=true)
     */
    protected $slot;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="text", nullable=true)
     */
    private $query;
    /**
     * @var string
     *
     * @ORM\Column(name="business_class", type="string", length=255, nullable=true)
     */
    private $businessClass;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\PageBundle\Entity\BasePage", inversedBy="widgets")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     *
     */
    protected $page;

    /**
     * Auto simple mode: joined entity
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\CoreBundle\Cached\Entity\EntityProxy", inversedBy="widget", cascade={"persist"})
     */
    protected $entity;

    /**
     *  Auto list mode: businessentity type
     * @var string
     * @ORM\Column(name="business_entity_name", type="string", nullable=true)
     *
     */
    protected $businessEntityName;

    /**
     * This property is not persisted, we use it to remember the page where the widget
     * is actually rendered.
     */
    protected $currentPage;

    /**
     * @var string
     *
     * @ORM\Column(name="fields", type="array")
     */
    protected $fields = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->entities = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * to string
     *
     * @return id
     */
    public function __toString()
    {
        return (string) $this->getId();
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
     * Set fields
     *
     * @param string $fields
     * @return EntityProxy
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get fields
     *
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set The Id
     *
     * @param integer $id The id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set slot
     *
     * @param string $slot
     * @return Widget
     */
    public function setSlot($slot)
    {
        $this->slot = $slot;

        return $this;
    }

    /**
     * Get slot
     *
     * @return string
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * Set page
     *
     * @param string $page
     * @return Widget
     */
    public function setPage($page)
    {
        $page->addWidget($this);
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Get businessEntity
     *
     * @return integer
     */
    public function getBusinessEntityName()
    {
        return $this->businessEntityName;
    }

    /**
     * Set businessEntityName
     *
     * @param String $businessEntityName The business entity name
     */
    public function setBusinessEntityName($businessEntityName)
    {
        $this->businessEntityName = $businessEntityName;
    }


    /**
     * Set the entity
     *
     * @param unknown $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        $entity->setWidget($this);
    }

    /**
     * Get the entity
     *
     * @return number
     */
    public function getEntity()
    {
        return $this->entity;
    }


    /**
     * Get the content
     *
     * @return unknown
     */
    public function getValue()
    {
        return $this->getContent();
    }

    /**
     * Set the current page
     *
     * @param Page $currentPage
     * @return \Victoire\Bundle\CoreBundle\Entity\Widget
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * Get the current page
     *
     * @return Page The current page
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
    /**
     * Get query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set query
     *
     * @param string $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Get businessClass
     *
     * @return string
     */
    public function getBusinessClass()
    {
        return $this->businessClass;
    }

    /**
     * Set businessClass
     *
     * @param string $businessClass
     * @return $this
     */
    public function setBusinessClass($businessClass)
    {
        $this->businessClass = $businessClass;
        return $this;
    }

    /**
     * Get the type of the object
     *
     * @return string The type
     */
    public function getType()
    {
        return $this->guessType();
    }

    /**
     * Guess the type of this by exploding and getting the last item
     *
     * @return String The guessed type
     */
    protected function guessType()
    {
        $type = explode('\\', get_class($this));

        return strtolower(preg_replace('/Widget/', '', end($type)));
    }
}
