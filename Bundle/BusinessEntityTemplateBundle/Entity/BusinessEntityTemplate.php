<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Entity;

use Victoire\Bundle\CoreBundle\Entity\BusinessEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Victoire\Bundle\PageBundle\Entity\Page;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BusinessEntityTemplate
 *
 * @ORM\Table("cms_business_entity_template")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessEntityTemplateBundle\Repository\BusinessEntityTemplateRepository")
 * @ORM\HasLifecycleCallbacks()
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
     * @ORM\Column(name="business_entity_id", type="string", length=255, nullable=false)
     */
    protected $businessEntityId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage", cascade={"persist", "remove",}, inversedBy="businessEntityTemplate")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", nullable=false)
     *
     */
    protected $page;

    /**
     * @var string
     *
     */
    protected $layout;

    /**
     * @ORM\Column(name="mandatory", type="boolean", nullable=false)
     *
     */
    protected $mandatory = true;

    protected $businessEntity = null;

    /**
     * contructor
     *
     **/
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

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
        $this->businessEntityId = $businessEntity->getId();
    }

    /**
     * Set the business entity id
     * @param string $businessEntityId
     */
    public function setBusinessEntityId($businessEntityId)
    {
        $this->businessEntityId = $businessEntityId;
    }

    /**
     * Get the business entity id
     *
     * @return string
     */
    public function getBusinessEntityId()
    {
        return $this->businessEntityId;
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

    /**
     * Set layout
     *
     * @param string $layout
     * @return Page
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get layout
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Prepersist
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->page = new BusinessEntityTemplatePage();
        $this->page->setTitle($this->getName());
        $this->page->setLayout($this->layout);
    }


    /**
     * Get the url of the page
     *
     * @return string The url
     */
    public function getPageUrl()
    {
        $page = $this->page;

        $url = $page->getUrl();

        return $url;
    }

    /**
     * Get the mandatory value
     *
     * @return boolean Is the template mandatory
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Set the mandatory value
     *
     * @param boolean $mandatory
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }
}