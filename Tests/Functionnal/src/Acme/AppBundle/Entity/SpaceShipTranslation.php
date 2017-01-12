<?php

namespace Acme\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model\Translatable\Translation;
use Victoire\Bundle\CoreBundle\Annotations as VIC;

/**
 * SpaceShipTranslation.
 *
 * @ORM\Entity
 * @ORM\Table("space_ship_translation")
 */
class SpaceShipTranslation
{
    use Translation;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=55)
     * @VIC\BusinessProperty("businessParameter")
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }
}
