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
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage", cascade={"persist", "remove"}, inversedBy="businessEntityTemplate")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", nullable=false)
     *
     */
    protected $template;

    /**
     * @ORM\OneToOne(targetEntity="Victoire\Bundle\PageBundle\Entity\Page", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="parent_page_id", referencedColumnName="id", nullable=false)
     *
     */
    protected $parentPage;

    /**
     * @var string
     * @ORM\Column(name="layout", type="string", length=255, nullable=false)
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
     * Get the template page
     *
     * @return Template The template page
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set the template page
     *
     * @param Page $page The template page
     */
    public function setTemplatePage(Template $page)
    {
        $this->template = $page;
    }

    /**
     * Get the parent page
     *
     * @return Page The parent page
     */
    public function getParentPage()
    {
        return $this->parentPage;
    }

    /**
     * Set the parent page
     *
     * @param Page $page The parent page
     */
    public function setParentPage(Page $page)
    {
        $this->parentPage = $page;
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
        $this->template = new BusinessEntityTemplatePage();
        $this->template->setTitle($this->getName());
        $this->template->setLayout($this->layout);

        $this->parentPage = new Page();
        $this->parentPage->setTitle($this->getName());
        $this->parentPage->setLayout($this->layout);
    }

    /**
     * Get the url of the page
     *
     * @return string The url
     */
    public function getParentPageUrl()
    {
        $page = $this->parentPage;

        $url = $page->getUrl();

        return $url;
    }

    /**
     * Get the url of the template
     *
     * @return string The url
     */
    public function getTemplateUrl()
    {
        $page = $this->template;

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