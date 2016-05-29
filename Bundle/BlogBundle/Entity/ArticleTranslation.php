<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Model\Translatable\Translation;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\CoreBundle\Annotations as VIC;

/**
 * Victoire ViewTranslation.
 *
 * @ORM\Entity()
 * @ORM\Table(name="vic_article_translations")
 */
class ArticleTranslation
{
    use Translation;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Groups({"search"})
     * @VIC\BusinessProperty({"textable", "businessParameter", "seoable"})
     */
    protected $name;

    /**
     * @var string
     *
     * @Gedmo\Slug(handlers={
     *     @Gedmo\SlugHandler(class="Victoire\Bundle\BusinessEntityBundle\Handler\TwigSlugHandler"
     * )},fields={"name"}, updatable=false, unique=false)
     * @ORM\Column(name="slug", type="string", length=255)
     * @VIC\BusinessProperty("businessParameter")
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @VIC\BusinessProperty({"textable", "seoable"})
     */
    private $description;

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return View
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return View
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
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set category.
     *
     * @param string $category
     *
     * @return Article
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
