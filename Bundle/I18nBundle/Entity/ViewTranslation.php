<?php

namespace Victoire\Bundle\I18nBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Model\Translatable\Translation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Victoire ViewTranslation.
 *
 * @ORM\Entity()
 * @ORM\Table(name="vic_view_translations")
 */
class ViewTranslation
{
    use Translation;

    /**
     * @Serializer\Groups({"search"})
     */
    protected $locale;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Groups({"search"})
     */
    protected $name;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="permalink", type="string", length=255, nullable=true)
     * @Serializer\Groups({"search"})
     */
    protected $permalink;

    /**
     * @var string
     *
     * @Gedmo\Slug(handlers={
     *     @Gedmo\SlugHandler(class="Victoire\Bundle\BusinessEntityBundle\Handler\TwigSlugHandler"
     * )},fields={"name"}, updatable=false, unique=false)
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var string
     *             This property is computed by the method PageSubscriber::buildUrl
     */
    protected $url;

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
     * Get permalink.
     *
     * @return string
     */
    public function getPermalink()
    {
        return $this->permalink;
    }

    /**
     * Set permalink.
     *
     * @param string $permalink
     *
     * @return View
     */
    public function setPermalink($permalink)
    {
        $this->permalink = $permalink;

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
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTranslatableEntityClass()
    {
        return '\\Victoire\\Bundle\\CoreBundle\\Entity\\View';
    }
}
