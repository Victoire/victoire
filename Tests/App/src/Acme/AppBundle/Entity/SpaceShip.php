<?php

namespace Acme\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessEntityBundle\Entity\Traits\BusinessEntityTrait;
use Victoire\Bundle\CoreBundle\Annotations as VIC;

/**
 * Mercenary.
 *
 * @ORM\Entity
 * @ORM\Table("space_ship")
 * @VIC\BusinessEntity({"Force", "Text"})
 */
class SpaceShip
{
    use BusinessEntityTrait;
    use Translatable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @VIC\BusinessProperty("businessParameter")
     */
    private $slug;
    /**
     * @VIC\BusinessProperty("businessParameter")
     */
    private $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->getName();
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
}
