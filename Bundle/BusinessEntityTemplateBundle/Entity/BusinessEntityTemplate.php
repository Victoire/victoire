<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Entity;

use Victoire\Bundle\CoreBundle\Entity\BusinessEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BusinessEntityTemplate
 *
 * @ORM\Table("cms_business_entity_template")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessEntityTemplateBundle\Repository\BusinessEntityTemplateRepository")
 */
class BusinessEntityTemplate
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
     * @ORM\Column(name="business_entity", type="string", length=255, nullable=false)
     */
    protected $businessEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\PageBundle\Entity\Page", cascade={"persist", "remove",})
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", nullable=false)
     *
     */
    protected $page;

    /**
     * Get the id
     *
     * @return string The id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the name
     *
     * @return string The name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the businessEntity
     *
     * @return BusinessEntity
     */
    public function getBusinessEntity()
    {
        return $this->businessEntity;
    }

    /**
     * Set the BusinessEntity
     *
     * @param BusinessEntity $businessEntity
     */
    public function setBusinessEntity(BusinessEntity $businessEntity)
    {
        $this->businessEntity = $businessEntity;
    }

    /**
     * Get the page
     *
     * @return Page The page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set the page
     *
     * @param Page $page The page
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
    }
}