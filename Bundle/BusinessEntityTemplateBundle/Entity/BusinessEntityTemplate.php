<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Entity;

use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Victoire\Bundle\PageBundle\Entity\Page;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\PageBundle\Entity\Template;

/**
 * BusinessEntityTemplate
 *
 * @ORM\Table("cms_page_business_entity_template")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessEntityTemplateBundle\Repository\BusinessEntityTemplateRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BusinessEntityTemplate extends Template
{
    use \Victoire\Bundle\QueryBundle\Entity\Traits\QueryTrait;

    const TYPE = 'businessEntityTemplate';

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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    protected $businessEntity = null;

    /**
     * contructor
     *
     **/
    public function __construct()
    {
        parent::__construct();
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

        //the business entity is an object that represents the type of business entity available (car, person, etc.)
        //the object has an id like a normal object
        //but in the previous versions we used the name of the businessEntity has an id
        //that is why this code can be ankward
        //@todo we should always used the object of type BusinessEntity instead of businessEntityName in the victoire
        $this->businessEntityName = $businessEntity->getId();
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
}