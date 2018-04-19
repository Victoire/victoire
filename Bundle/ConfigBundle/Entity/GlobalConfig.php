<?php

namespace Victoire\Bundle\ConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Victoire\Bundle\ConfigBundle\Validator\Constraints\SemanticalOrganizationJsonLD;
use Victoire\Bundle\MediaBundle\Entity\Media;

/**
 * GlobalConfig.
 *
 * @ORM\Table(name="vic_global_config")
 * @ORM\Entity
 */
class GlobalConfig
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="head", type="text", nullable=true)
     */
    private $head;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="logo_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $logo;

    /**
     * @var string
     * @Assert\Regex(pattern="/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/")
     * @ORM\Column(name="mainColor", type="string", length=7, nullable=true)
     */
    private $mainColor;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="/%page.title%/", message="victoire.config.global.metaTitlePattern.invalid")
     * @ORM\Column(name="metaTitlePattern", type="string", length=255, nullable=true)
     */
    private $metaTitlePattern;

    /**
     * @var string
     * @SemanticalOrganizationJsonLD
     * @ORM\Column(name="organizationJsonLD", type="text", nullable=true)
     */
    private $organizationJsonLD;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set head.
     *
     * @param string $head
     *
     * @return GlobalConfig
     */
    public function setHead($head): self
    {
        $this->head = $head;

        return $this;
    }

    /**
     * Get head.
     *
     * @return string
     */
    public function getHead(): ?string
    {
        return $this->head;
    }

    /**
     * Set logo.
     *
     * @param Media $logo
     *
     * @return GlobalConfig
     */
    public function setLogo(Media $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo.
     *
     * @return Media
     */
    public function getLogo(): ?Media
    {
        return $this->logo;
    }

    /**
     * Set metaTitlePattern.
     *
     * @param string $metaTitlePattern
     *
     * @return GlobalConfig
     */
    public function setMetaTitlePattern($metaTitlePattern): self
    {
        $this->metaTitlePattern = $metaTitlePattern;

        return $this;
    }

    /**
     * Get metaTitlePattern.
     *
     * @return string
     */
    public function getMetaTitlePattern(): ?string
    {
        return $this->metaTitlePattern;
    }

    /**
     * Set organizationJsonLD.
     *
     * @param string $organizationJsonLD
     *
     * @return GlobalConfig
     */
    public function setOrganizationJsonLD($organizationJsonLD): self
    {
        $this->organizationJsonLD = $organizationJsonLD;

        return $this;
    }

    /**
     * Get organizationJsonLD.
     *
     * @return string
     */
    public function getOrganizationJsonLD(): ?string
    {
        return $this->organizationJsonLD;
    }

    /**
     * @return string
     */
    public function getMainColor(): ?string
    {
        return $this->mainColor;
    }

    /**
     * @param string $mainColor
     *
     * @return GlobalConfig
     */
    public function setMainColor(string $mainColor): self
    {
        $this->mainColor = $mainColor;

        return $this;
    }
}
