<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Victoire\Bundle\CoreBundle\Entity\Traits\BusinessEntityTrait;
use Victoire\Bundle\MediaBundle\Entity\Media;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * PostPage
 *
 * @ORM\Entity
 * @ORM\Table("vic_article")
 *
 * @VIC\BusinessEntity({"Redactor", "Listing", "BlogArticles", "Title", "CKEditor", "Text", "UnderlineTitle", "Cover", "Image", "Authorship", "ArticleList"})
 */
class Article extends BasePage
{
    use BusinessEntityTrait;

    const TYPE = 'article';

    /**
     * Title is inherited from Page, just add the BusinessProperty annotation
     * @var string
     *
     * @Assert\NotBlank()
     * @VIC\BusinessProperty("textable")
     */
    protected $name;
    /**
     * @var string
     *
     * @VIC\BusinessProperty("businessIdentifier")
     */
    protected $slug;
    /**
     * @var string
     *
     * @VIC\BusinessProperty("businessIdentifier")
     */
    protected $id;

    /**
     * Description is inherited from Page, just add the BusinessProperty annotation
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
    * @VIC\BusinessProperty("textable")
    */
    protected $publishedAt;

    /**
     * @var string
     * Title is inherited from Page, just add the BusinessProperty annotation
     *
     * @VIC\BusinessProperty("textable")
     */
    protected $url;

    /**
     * @var string
     * Author is inherited from Page, just add the BusinessProperty annotation
     *
     * @VIC\BusinessProperty("textable")
     */
    protected $author;

    /**
     * Tags of the article
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="articles")
     * @ORM\JoinTable(name="vic_article_tags")
     * @Assert\Valid()
     */
    protected $tags;

    /**
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\BlogBundle\Entity\Blog", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $blog;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="CASCADE")
     * @VIC\BusinessProperty("imageable")
     *
     */
    protected $image;

    /**
     * @VIC\BusinessProperty("textable")
     */
    protected $categoryTitle;

    /**
    * @VIC\BusinessProperty("textable")
    */
    protected $publishedAtString;

    /**
    * @VIC\BusinessProperty("textable")
    */
    protected $authorAvatar;

    /**
    * @VIC\BusinessProperty("textable")
    */
    protected $authorFullName;

    /**
     * Set description
     *
     * @param string $description
     *
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
     *
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
     * Get the blog
     *
     * @return String
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Set the blog
     *
     * @param string $blog
     */
    public function setBlog($blog)
    {
        $this->blog = $blog;
        $this->setParent($blog);
    }

    /**
     * Set tags
     *
     * @param string $tags
     *
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
     *
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
     *
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

    /**
     * Set image
     * @param string $image
     *
     * @return WidgetImage
     */
    public function setImage(Media $image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Get businessEntity
     *
     * @return string
     */
    public function getBusinessEntity()
    {
        return $this;
    }

    /**
     * Get categoryTitle
     *
     * @return string
     */
    public function getCategoryTitle()
    {
        return $this->category->getTitle();
    }

    /**
     * Get publishedAtString
     *
     * @return string
     */
    public function getPublishedAtString()
    {
        setlocale(LC_TIME, "fr_FR");
        return strftime('%d %B %Y', $this->publishedAt->getTimestamp());
    }

    public function getAuthorAvatar()
    {
        $email = $this->author->getEmail();
        return "http://www.gravatar.com/avatar/" . md5($email) . "?s=70";
    }
    public function getAuthorFullname()
    {
        return $this->author->getFullname();
    }
}
