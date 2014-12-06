<?php
namespace Victoire\Bundle\I18nBundle\Entity\Traits;

use Victoire\Bundle\I18nBundle\Entity\I18n;

trait I18nTrait
{
    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="\Victoire\Bundle\I18nBundle\Entity\I18n")
     */
    protected $i18n;

    public function getI18n()
    {
        return $this->i18n;
    }

    public function setI18n(I18n $i18n)
    {
        $this->i18n = $i18n;

        return $this;
    }
}
