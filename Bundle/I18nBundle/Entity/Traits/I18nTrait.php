<?php
namespace Victoire\Bundle\I18nBundle\Entity\Traits;

trait I18nTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", name="locale", length=255)
     */
    protected $locale;

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
