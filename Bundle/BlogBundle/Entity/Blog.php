<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * PostPage.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BlogBundle\Repository\BlogRepository"))
 * @ORM\Table("vic_blog")
 */
class Blog extends Page
{
    const TYPE = 'blog';

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Category", mappedBy="blog", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $categories;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\BlogBundle\Entity\Article", mappedBy="blog")
     */
    protected $articles;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\BlogBundle\Entity\Tag", mappedBy="blog")
     */
    protected $tags;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param $articles
     *
     * @return $this
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * @param Article $article
     *
     * @return $this
     */
    public function addArticle(Article $article)
    {
        $article->setBlog($this);
        $this->articles->add($article);

        return $this;
    }

    /**
     * Set categories.
     *
     * @param array $categories
     *
     * @return Blog
     */
    public function setCategories($categories)
    {
        foreach ($categories as $category) {
            $category->setBlog($this);
        }
        $this->categories = $categories;

        return $this;
    }

    /**
     * @param Category $category
     *
     * @return $this
     */
    public function addCategorie(Category $category)
    {
        $category->setBlog($this);
        $this->categories->add($category);

        return $this;
    }

    /**
     * Remove category.
     *
     * @param string $category
     *
     * @return Blog
     */
    public function removeCategorie($category)
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * Get categories.
     *
     * @return string
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Get root categories.
     *
     * @return string
     */
    public function getRootCategories()
    {
        $rootCategories = [];
        foreach ($this->categories as $categories) {
            if ($categories->getLvl() == 0) {
                $rootCategories[] = $categories;
            }
        }

        return $rootCategories;
    }

    /**
     * @param Category $rootCategory
     *
     * @return $this
     */
    public function addRootCategory(Category $rootCategory)
    {
        $rootCategory->setBlog($this);
        $this->categories->add($rootCategory);

        return $this;
    }

    /**
     * Remove rootCategory.
     *
     * @param string $rootCategory
     *
     * @return Blog
     */
    public function removeRootCategory($rootCategory)
    {
        $this->categories->removeElement($rootCategory);

        return $this;
    }

    /**
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param Tag $tag
     *
     * @return Tag
     */
    public function addTag(Tag $tag)
    {
        $tag->setBlog($this);
        $this->tags->add($tag);

        return $tag;
    }
}
