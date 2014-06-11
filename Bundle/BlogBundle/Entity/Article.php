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
 * @VIC\BusinessEntity({"widgetredactor", "themeredactornewspaper", "widgetlisting", "widgetarchive", "themelistingblogarticles"})
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
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="articles")
     */
    protected $category;

    /**
    * @var datetime $publishedAt
    *
    * @ORM\Column(name="publishedAt", type="datetime")
    * @VIC\BusinessProperty("datable")
    */
    protected $publishedAt;

    /**
     * @var string
     * Title is inherited from BasePage, just add the BusinessProperty annotation
     *
     * @VIC\BusinessProperty("textable")
     */
    protected $url;

    /**
     * @var string
     * Author is inherited from BasePage, just add the BusinessProperty annotation
     *
     * @VIC\BusinessProperty("textable")
     */
    protected $author;

    /**
     * Tags of the article
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="articles")
     */
    protected $tags;

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
     * Set category
     *
     * @param string $category
     * @return Article
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
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


    /**
     * Set tags
     *
     * @param string $tags
     * @return Article
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Add tag
     *
     * @param string $tag
     * @return Article
     */
    public function addTag($tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param string $tag
     * @return Article
     */
    public function removeTag($tag)
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }


}
