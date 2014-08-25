<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * PostPage
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BlogBundle\Repository\BlogRepository"))
 * @ORM\Table("vic_blog")
 *
 */
class Blog extends BasePage
{
    const TYPE = 'blog';

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\BlogBundle\Entity\Article", mappedBy="blog")
     */
    protected $articles;

    public function getArticles() { return $this->articles; }
    public function setArticles($articles) { $this->articles = $articles; return $this; }
}
