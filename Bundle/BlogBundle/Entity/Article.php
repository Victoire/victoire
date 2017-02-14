<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\BusinessEntityBundle\Entity\Traits\BusinessEntityTrait;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Annotations as VIC;
use Victoire\Bundle\PageBundle\Entity\PageStatus;

/**
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BlogBundle\Repository\ArticleRepository"))
 * @ORM\Table("vic_article")
 * @VIC\BusinessEntity({"Date", "Force", "Redactor", "Listing", "BlogArticles", "Title", "CKEditor", "Text", "UnderlineTitle", "Cover", "Image", "Authorship", "ArticleList", "SliderNav", "Render"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Article
{
    use BusinessEntityTrait;
    use TimestampableEntity;
    use Translatable;

    /**
     * @VIC\BusinessProperty("businessParameter")
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @deprecated
     * Title is inherited from Page, just add the BusinessProperty annotation.
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @deprecated
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @deprecated
     * Description is inherited from Page, just add the BusinessProperty annotation.
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @deprecated
     *
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="CASCADE")
     * @VIC\BusinessProperty("imageable")
     */
    private $image;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    protected $status;

    /**
     * Categories of the article.
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="articles")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @VIC\BusinessProperty({"textable", "seoable"})
     */
    private $category;

    /**
     * @var datetime
     *
     * @ORM\Column(name="publishedAt", type="datetime", nullable=true)
     * @VIC\BusinessProperty({"dateable", "textable"})
     */
    private $publishedAt;

    /**
     * This relation is dynamically added by ArticleSubscriber
     * The property is needed here.
     *
     * @VIC\BusinessProperty({"textable", "seoable"})
     */
    private $author;

    /**
     * Tags of the article.
     *
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
     * @ORM\ManyToOne(targetEntity="ArticleTemplate")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\NotNull()
     */
    private $template;

    /**
     * @VIC\BusinessProperty("textable")
     */
    private $categoryTitle;

    /**
     * @VIC\BusinessProperty("textable")
     */
    private $publishedAtString;

    /**
     * @VIC\BusinessProperty({"textable", "imageable"})
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
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * to string method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->status = PageStatus::DRAFT;
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
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set category.
     *
     * @param Category $category
     *
     * @return Article
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get the published at property.
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        if ($this->status == PageStatus::PUBLISHED && $this->publishedAt === null) {
            $this->setPublishedAt($this->getCreatedAt());
        }

        return $this->publishedAt;
    }

    /**
     * Set publishedAt.
     *
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
     * Get deletedAt.
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set deletedAt.
     *
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
     * Get the blog.
     *
     * @return Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Set the blog.
     *
     * @param Blog $blog
     */
    public function setBlog(Blog $blog)
    {
        $this->blog = $blog;
    }

    /**
     * Set tags.
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
     * Add tag.
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
     * Remove tag.
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
     * Get tags.
     *
     * @return [Tag]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Get businessEntity.
     *
     * @return Article
     */
    public function getBusinessEntity()
    {
        return $this;
    }

    /**
     * Set template.
     *
     * @param ArticleTemplate $template
     *
     * @return Article
     */
    public function setTemplate(ArticleTemplate $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template.
     *
     * @return ArticleTemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set status.
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        if ($status == PageStatus::PUBLISHED && $this->publishedAt === null) {
            $this->setPublishedAt(new \DateTime());
        }
        $this->status = $status;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get categoryTitle.
     *
     * @return string
     */
    public function getCategoryTitle()
    {
        $this->categoryTitle = $this->category ? $this->category->getTitle() : null;

        return $this->categoryTitle;
    }

    /**
     * Get publishedAtString.
     *
     * @return string
     */
    public function getPublishedAtString()
    {
        setlocale(LC_TIME, 'fr_FR');

        if ($this->publishedAt) {
            return strftime('%d %B %Y', $this->publishedAt->getTimestamp());
        } else {
            return '';
        }
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

    public function getAuthorAvatar()
    {
        $this->authorAvatar = 'http://www.gravatar.com/avatar/'.md5($this->author->getEmail()).'?s=70';

        return $this->authorAvatar;
    }

    public function getAuthorFullname()
    {
        $this->authorFullName = $this->author->getFullname();

        return $this->authorFullName;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    public function getName()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(), 'getName');
    }

    public function setName($name, $locale = null)
    {
        $this->translate($locale, false)->setName($name);
        $this->mergeNewTranslations();
    }

    public function getSlug()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(), 'getSlug');
    }

    public function setSlug($slug, $locale = null)
    {
        $this->translate($locale, false)->setSlug($slug);
        $this->mergeNewTranslations();
    }

    public function getDescription()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(), 'getDescription');
    }

    public function setDescription($description, $locale = null)
    {
        $this->translate($locale, false)->setDescription($description);
        $this->mergeNewTranslations();
    }

    public function getImage()
    {
        return PropertyAccess::createPropertyAccessor()->getValue($this->translate(), 'getImage');
    }

    public function setImage($image, $locale = null)
    {
        $this->translate($locale, false)->setImage($image);
        $this->mergeNewTranslations();
    }
}
