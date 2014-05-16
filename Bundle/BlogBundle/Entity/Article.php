<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Victoire\Bundle\PageBundle\Entity\Page;
use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PostPage
 *
 * @ORM\Entity
 *
 * @VIC\BusinessEntity({"widgetredactor", "themeredactornewspaper", "widgetimage", "widgetlisting", "widgetarchive"})
 */
class Article extends Page
{
    use \Victoire\Bundle\CoreBundle\Entity\Traits\BusinessEntityTrait;

    const TYPE = 'article';

    /**
     * Title is inherited from BasePage, just add the BusinessProperty annotation
     * @var string
     *
     * @Assert\NotBlank()
     * @VIC\BusinessProperty("textable")
     */
    protected $title;

    /**
     * Description is inherited from BasePage, just add the BusinessProperty annotation
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="description", type="text")
     * @VIC\BusinessProperty("textable")
     */
    protected $description;

    /**
     * Categories of the article
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="articles")
     */
    protected $categories;

    /**
    * @var datetime $publishedAt
    *
    * @ORM\Column(name="publishedAt", type="datetime")
    * @VIC\BusinessProperty("date")
    */
    protected $publishedAt;

    /**
     * Set description
     *
     * @param string $description
     * @return PostPage
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Set categories
     *
     * @param string $categories
     * @return Article
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Add category
     *
     * @param string $category
     * @return Article
     */
    public function addCategory($category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param string $category
     * @return Article
     */
    public function removeCategory($category)
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * Get categories
     *
     * @return string
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Get the published at property
     *
     * @return DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Get the title
     *
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}
