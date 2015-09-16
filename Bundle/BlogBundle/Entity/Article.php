<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Victoire\Bundle\BusinessEntityBundle\Entity\Traits\BusinessEntityTrait;
use Victoire\Bundle\MediaBundle\Entity\Media;

/**
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BlogBundle\Repository\ArticleRepository"))
 * @ORM\Table("vic_article")
 * @VIC\BusinessEntity({"Force", "Redactor", "Listing", "BlogArticles", "Title", "CKEditor", "Text", "UnderlineTitle", "Cover", "Image", "Authorship", "ArticleList"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Article
{
    use BusinessEntityTrait;
    use TimestampableEntity;

    const DRAFT       = "draft";
    const PUBLISHED   = "published";
    const UNPUBLISHED = "unpublished";
    const SCHEDULED   = "scheduled";

    /**
     * @VIC\BusinessProperty("businessParameter")
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Title is inherited from Page, just add the BusinessProperty annotation
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     * @VIC\BusinessProperty({"textable", "businessParameter", "seoable"})
     */
    private $name;

    /**
     * @ORM\Column(name="slug", type="string", length=255)
     * @Gedmo\Slug(fields={"name"}, updatable=false, unique=false)
     * @VIC\BusinessProperty("businessParameter")
     */
    private $slug;

    /**
     * Description is inherited from Page, just add the BusinessProperty annotation
     * @ORM\Column(name="description", type="text", nullable=true)
     * @VIC\BusinessProperty({"textable", "seoable"})
     */
    private $description;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    protected $status;

    /**
     * Categories of the article
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="articles")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @VIC\BusinessProperty({"textable", "seoable"})
     */
    private $category;

    /**
     * @var datetime $publishedAt
     *
     * @ORM\Column(name="publishedAt", type="datetime", nullable=true)
     * @VIC\BusinessProperty({"dateable", "textable"})
     */
    private $publishedAt;

    /**
     * This relation is dynamically added by ArticleSubscriber
     * The property is needed here
     * @VIC\BusinessProperty({"textable", "seoable"})
     */
    private $author;

    /**
     * Tags of the article
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="articles")
     * @ORM\JoinTable(name="vic_article_tags")
     * @Assert\Valid()
     */
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\BlogBundle\Entity\Blog", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $blog;

    /**
     * @var BusinessTemplate
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate")
     * @ORM\JoinColumn(name="pattern_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\NotNull()
     */
    private $pattern;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="CASCADE")
     * @VIC\BusinessProperty("imageable")
     */
    private $image;

    /**
     * @VIC\BusinessProperty("textable")
     */
    private $categoryTitle;

    /**
     * @VIC\BusinessProperty("textable")
     */
    private $publishedAtString;

    /**
     * @VIC\BusinessProperty("textable")
     */
    private $authorAvatar;

    /**
     * @VIC\BusinessProperty("textable")
     */
    private $authorFullName;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * to string method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Constructor
     *
     *
     */
    public function __construct()
    {
        $this->status = self::DRAFT;
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
     * Set id
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Article
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
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        if ($this->status == self::PUBLISHED && $this->publishedAt === null) {
            $this->setPublishedAt($this->getCreatedAt());
        }

        return $this->publishedAt;
    }

    /**
     * Set publishedAt
     * @param \DateTime $publishedAt
     *
     * @return $this
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set deletedAt
     * @param \DateTime $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get the blog
     *
     * @return Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Set the blog
     *
     * @param Blog $blog
     */
    public function setBlog(Blog $blog)
    {
        $this->blog = $blog;
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
     * @param Media $image
     *
     * @return Article
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
     * @return Article
     */
    public function getBusinessEntity()
    {
        return $this;
    }

    /**
     * Set pattern
     * @param BusinessTemplate $pattern
     *
     * @return Article
     */
    public function setPattern(BusinessTemplate $pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get pattern
     *
     * @return BusinessTemplate
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set status
     *
     * @param status $status
     */
    public function setStatus($status)
    {
        if ($status == self::PUBLISHED && $this->publishedAt === null) {
            $this->setPublishedAt(new \DateTime());
        }
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get categoryTitle
     *
     * @return string
     */
    public function getCategoryTitle()
    {
        $this->categoryTitle = $this->category ? $this->category->getTitle() : null;

        return $this->categoryTitle;
    }

    /**
     * Get publishedAtString
     *
     * @return string
     */
    public function getPublishedAtString()
    {
        setlocale(LC_TIME, "fr_FR");

        if ($this->publishedAt) {
            return strftime('%d %B %Y', $this->publishedAt->getTimestamp());
        }
        else {
            return "";
        }
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set author
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

    public function getAuthorAvatar()
    {
        $this->authorAvatar = "http://www.gravatar.com/avatar/".md5($this->author->getEmail())."?s=70";

        return $this->authorAvatar;
    }

    public function getAuthorFullname()
    {
        $this->authorFullName = $this->author->getFullname();

        return $this->authorFullName;
    }
}
