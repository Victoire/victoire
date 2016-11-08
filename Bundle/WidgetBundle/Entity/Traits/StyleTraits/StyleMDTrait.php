<?php

namespace Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTraits;

use Victoire\Bundle\MediaBundle\Entity\Media;
use Doctrine\ORM\Mapping as ORM;

trait StyleMDTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="container_margin_md", type="string", length=255, nullable=true)
     */
    protected $containerMarginMD;

    /**
     * @var string
     *
     * @ORM\Column(name="container_padding_md", type="string", length=255, nullable=true)
     */
    protected $containerPaddingMD;

    /**
     * @var string
     *
     * @ORM\Column(name="container_width_md", type="string", length=255, nullable=true)
     */
    protected $containerWidthMD;

    /**
     * @var string
     *
     * @ORM\Column(name="container_height_md", type="string", length=255, nullable=true)
     */
    protected $containerHeightMD;

    /**
     * @var string
     *
     * @ORM\Column(name="text_align_md", type="string", length=15, nullable=true)
     */
    protected $textAlignMD;

    /**
     * @var string
     *
     * @deprecated
     * @ORM\Column(name="container_background_md", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundMD;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_type_md", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundTypeMD;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_repeat_md", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundRepeatMD;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_position_md", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundPositionMD;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_size_md", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundSizeMD;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_color_md", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundColorMD;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="container_background_image_md_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $containerBackgroundImageMD;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_overlay_md", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundOverlayMD;

    /**
     * @return string
     */
    public function getContainerMarginMD()
    {
        return $this->containerMarginMD;
    }

    /**
     * @param string $containerMarginMD
     *
     * @return $this
     */
    public function setContainerMarginMD($containerMarginMD)
    {
        $this->containerMarginMD = $containerMarginMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerPaddingMD()
    {
        return $this->containerPaddingMD;
    }

    /**
     * @param string $containerPaddingMD
     *
     * @return $this
     */
    public function setContainerPaddingMD($containerPaddingMD)
    {
        $this->containerPaddingMD = $containerPaddingMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerWidthMD()
    {
        return $this->containerWidthMD;
    }

    /**
     * @param string $containerWidthMD
     *
     * @return $this
     */
    public function setContainerWidthMD($containerWidthMD)
    {
        $this->containerWidthMD = $containerWidthMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerHeightMD()
    {
        return $this->containerHeightMD;
    }

    /**
     * @param string $containerHeightMD
     *
     * @return $this
     */
    public function setContainerHeightMD($containerHeightMD)
    {
        $this->containerHeightMD = $containerHeightMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getTextAlignMD()
    {
        return $this->textAlignMD;
    }

    /**
     * @param string $textAlignMD
     *
     * @return $this
     */
    public function setTextAlignMD($textAlignMD)
    {
        $this->textAlignMD = $textAlignMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundMD()
    {
        return $this->containerBackgroundMD;
    }

    /**
     * @param string $containerBackgroundMD
     *
     * @return $this
     */
    public function setContainerBackgroundMD($containerBackgroundMD)
    {
        $this->containerBackgroundMD = $containerBackgroundMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundTypeMD()
    {
        return $this->containerBackgroundTypeMD;
    }

    /**
     * @param string $containerBackgroundTypeMD
     *
     * @return $this
     */
    public function setContainerBackgroundTypeMD($containerBackgroundTypeMD)
    {
        $this->containerBackgroundTypeMD = $containerBackgroundTypeMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundRepeatMD()
    {
        return $this->containerBackgroundRepeatMD;
    }

    /**
     * @param string $containerBackgroundRepeatMD
     *
     * @return $this
     */
    public function setContainerBackgroundRepeatMD($containerBackgroundRepeatMD)
    {
        $this->containerBackgroundRepeatMD = $containerBackgroundRepeatMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundPositionMD()
    {
        return $this->containerBackgroundPositionMD;
    }

    /**
     * @param string $containerBackgroundPositionMD
     *
     * @return $this
     */
    public function setContainerBackgroundPositionMD($containerBackgroundPositionMD)
    {
        $this->containerBackgroundPositionMD = $containerBackgroundPositionMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundSizeMD()
    {
        return $this->containerBackgroundSizeMD;
    }

    /**
     * @param string $containerBackgroundSizeMD
     *
     * @return $this
     */
    public function setContainerBackgroundSizeMD($containerBackgroundSizeMD)
    {
        $this->containerBackgroundSizeMD = $containerBackgroundSizeMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundColorMD()
    {
        return $this->containerBackgroundColorMD;
    }

    /**
     * @param string $containerBackgroundColorMD
     *
     * @return $this
     */
    public function setContainerBackgroundColorMD($containerBackgroundColorMD)
    {
        $this->containerBackgroundColorMD = $containerBackgroundColorMD;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundImageMD()
    {
        return $this->containerBackgroundImageMD;
    }

    /**
     * Set image.
     *
     * @param Media $image
     *
     * @return $this
     */
    public function setContainerBackgroundImageMD(Media $image = null)
    {
        $this->containerBackgroundImageMD = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundOverlayMD()
    {
        return $this->containerBackgroundOverlayMD;
    }

    /**
     * @param string $containerBackgroundOverlayMD
     *
     * @return $this
     */
    public function setContainerBackgroundOverlayMD($containerBackgroundOverlayMD)
    {
        $this->containerBackgroundOverlayMD = $containerBackgroundOverlayMD;

        return $this;
    }
}
