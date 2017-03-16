<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Victoire\Bundle\BusinessEntityBundle\Entity\Traits\BusinessEntityTrait;
use Victoire\Bundle\CoreBundle\Annotations as VIC;

/**
 * Tag.
 *
 * @ORM\Table("vic_tag")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BlogBundle\Repository\TagRepository")
 * @VIC\BusinessEntity("Listing")
 */
class Tag
{
    use BusinessEntityTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @ORM\ManyToMany(targetEntity="Article", mappedBy="tags")
     */
    protected $articles;

    /**
     * @ORM\ManyToOne(targetEntity="Blog", inversedBy="tags")
     */
    protected $blog;

    /**
     * undocumented function.
     *
     * @return string
     *
     * @author
     **/
    public function __toString()
    {
        return $this->getTitle();
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
     * Set title.
     *
     * @param string $title
     *
     * @return Tag
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Tag
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set pages.
     *
     * @param \stdClass $pages
     *
     * @return Tag
     */
    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * Get pages.
     *
     * @return \stdClass
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set articles.
     *
     * @param string $articles
     *
     * @return Tag
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * Get articles.
     *
     * @return string
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @return Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * @param Blog $blog
     */
    public function setBlog(Blog $blog)
    {
        $this->blog = $blog;
    }
}
