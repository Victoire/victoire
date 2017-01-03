<?php

namespace Victoire\Bundle\BlogBundle\Entity;

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
     * @ORM\OneToMany(targetEntity="Category", mappedBy="blog", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $categories;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\BlogBundle\Entity\Article", mappedBy="blog")
     */
    protected $articles;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\BlogBundle\Entity\Tag", mappedBy="blog")
     */
    protected $tags;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getArticles()
    {
        return $this->articles;
    }

    public function setArticles($articles)
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * Set categories.
     *
     * @param string $categories
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
     * Add category.
     *
     * @param string $category
     *
     * @return Blog
     */
    public function addCategorie($category)
    {
        $category->setBlog($this);
        $this->categories[] = $category;

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
     * Add rootCategory.
     *
     * @param string $rootCategory
     *
     * @return Blog
     */
    public function addRootCategory($rootCategory)
    {
        $rootCategory->setBlog($this);
        $this->categories[] = $rootCategory;

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
}
