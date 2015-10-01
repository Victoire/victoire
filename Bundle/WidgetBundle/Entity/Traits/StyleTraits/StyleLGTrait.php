<?php

namespace Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTraits;

use Victoire\Bundle\MediaBundle\Entity\Media;

trait StyleLGTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="container_margin_lg", type="string", length=255, nullable=true)
     */
    protected $containerMarginLG;

    /**
     * @var string
     *
     * @ORM\Column(name="container_padding_lg", type="string", length=255, nullable=true)
     */
    protected $containerPaddingLG;

    /**
     * @var string
     *
     * @ORM\Column(name="container_width_lg", type="string", length=255, nullable=true)
     */
    protected $containerWidthLG;

    /**
     * @var string
     *
     * @ORM\Column(name="container_height_lg", type="string", length=255, nullable=true)
     */
    protected $containerHeightLG;

    /**
     * @var string
     *
     * @ORM\Column(name="text_align_lg", type="string", length=15, nullable=true)
     */
    protected $textAlignLG;

    /**
     * @var string
     *
     * @deprecated
     * @ORM\Column(name="container_background_lg", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundLG;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_type_lg", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundTypeLG;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_repeat_lg", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundRepeatLG;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_position_lg", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundPositionLG;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_size_lg", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundSizeLG;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_color_lg", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundColorLG;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="container_background_image_lg_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $containerBackgroundImageLG;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_overlay_lg", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundOverlayLG;

    /**
     * @return string
     */
    public function getContainerMarginLG()
    {
        return $this->containerMarginLG;
    }

    /**
     * @param string $containerMarginLG
     *
     * @return $this
     */
    public function setContainerMarginLG($containerMarginLG)
    {
        $this->containerMarginLG = $containerMarginLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerPaddingLG()
    {
        return $this->containerPaddingLG;
    }

    /**
     * @param string $containerPaddingLG
     *
     * @return $this
     */
    public function setContainerPaddingLG($containerPaddingLG)
    {
        $this->containerPaddingLG = $containerPaddingLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerWidthLG()
    {
        return $this->containerWidthLG;
    }

    /**
     * @param string $containerWidthLG
     *
     * @return $this
     */
    public function setContainerWidthLG($containerWidthLG)
    {
        $this->containerWidthLG = $containerWidthLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerHeightLG()
    {
        return $this->containerHeightLG;
    }

    /**
     * @param string $containerHeightLG
     *
     * @return $this
     */
    public function setContainerHeightLG($containerHeightLG)
    {
        $this->containerHeightLG = $containerHeightLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getTextAlignLG()
    {
        return $this->textAlignLG;
    }

    /**
     * @param string $textAlignLG
     *
     * @return $this
     */
    public function setTextAlignLG($textAlignLG)
    {
        $this->textAlignLG = $textAlignLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundLG()
    {
        return $this->containerBackgroundLG;
    }

    /**
     * @param string $containerBackgroundLG
     *
     * @return $this
     */
    public function setContainerBackgroundLG($containerBackgroundLG)
    {
        $this->containerBackgroundLG = $containerBackgroundLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundTypeLG()
    {
        return $this->containerBackgroundTypeLG;
    }

    /**
     * @param string $containerBackgroundTypeLG
     *
     * @return $this
     */
    public function setContainerBackgroundTypeLG($containerBackgroundTypeLG)
    {
        $this->containerBackgroundTypeLG = $containerBackgroundTypeLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundRepeatLG()
    {
        return $this->containerBackgroundRepeatLG;
    }

    /**
     * @param string $containerBackgroundRepeatLG
     *
     * @return $this
     */
    public function setContainerBackgroundRepeatLG($containerBackgroundRepeatLG)
    {
        $this->containerBackgroundRepeatLG = $containerBackgroundRepeatLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundPositionLG()
    {
        return $this->containerBackgroundPositionLG;
    }

    /**
     * @param string $containerBackgroundPositionLG
     *
     * @return $this
     */
    public function setContainerBackgroundPositionLG($containerBackgroundPositionLG)
    {
        $this->containerBackgroundPositionLG = $containerBackgroundPositionLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundSizeLG()
    {
        return $this->containerBackgroundSizeLG;
    }

    /**
     * @param string $containerBackgroundSizeLG
     *
     * @return $this
     */
    public function setContainerBackgroundSizeLG($containerBackgroundSizeLG)
    {
        $this->containerBackgroundSizeLG = $containerBackgroundSizeLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundColorLG()
    {
        return $this->containerBackgroundColorLG;
    }

    /**
     * @param string $containerBackgroundColorLG
     *
     * @return $this
     */
    public function setContainerBackgroundColorLG($containerBackgroundColorLG)
    {
        $this->containerBackgroundColorLG = $containerBackgroundColorLG;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundImageLG()
    {
        return $this->containerBackgroundImageLG;
    }

    /**
     * Set image.
     *
     * @param Media $image
     *
     * @return $this
     */
    public function setContainerBackgroundImageLG(Media $image = null)
    {
        $this->containerBackgroundImageLG = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundOverlayLG()
    {
        return $this->containerBackgroundOverlayLG;
    }

    /**
     * @param string $containerBackgroundOverlayLG
     *
     * @return $this
     */
    public function setContainerBackgroundOverlayLG($containerBackgroundOverlayLG)
    {
        $this->containerBackgroundOverlayLG = $containerBackgroundOverlayLG;

        return $this;
    }
}
