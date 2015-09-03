<?php
namespace Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTraits;

use Victoire\Bundle\MediaBundle\Entity\Media;

trait StyleXSTrait {

    /**
     * @var string
     *
     * @ORM\Column(name="container_margin_xs", type="string", length=255, nullable=true)
     */
    protected $containerMarginXS;

    /**
     * @var string
     *
     * @ORM\Column(name="container_padding_xs", type="string", length=255, nullable=true)
     */
    protected $containerPaddingXS;

    /**
     * @var string
     *
     * @ORM\Column(name="container_width_xs", type="string", length=255, nullable=true)
     */
    protected $containerWidthXS;

    /**
     * @var string
     *
     * @ORM\Column(name="text_align_xs", type="string", length=15, nullable=true)
     */
    protected $textAlignXS;

    /**
     * @var string
     * @deprecated
     * @ORM\Column(name="container_background_xs", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundXS;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_type_xs", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundTypeXS;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_repeat_xs", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundRepeatXS;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_position_xs", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundPositionXS;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_size_xs", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundSizeXS;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_color_xs", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundColorXS;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="container_background_image_xs_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $containerBackgroundImageXS;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_overlay_xs", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundOverlayXS;

    /**
     * Delete all XS background
     */
    public function deleteBackgroundXS() {
        $this->containerBackgroundXS = null;
        $this->containerBackgroundTypeXS = null;
        $this->containerBackgroundColorXS = null;
        $this->containerBackgroundImageXS = null;
        $this->containerBackgroundRepeatXS = null;
        $this->containerBackgroundSizeXS = null;
        $this->containerBackgroundPositionXS = null;
        $this->containerBackgroundOverlayXS = null;
    }

    /**
     * @return string
     */
    public function getContainerMarginXS()
    {
        return $this->containerMarginXS;
    }

    /**
     * @param string $containerMarginXS
     * @return $this
     */
    public function setContainerMarginXS($containerMarginXS)
    {
        $this->containerMarginXS = $containerMarginXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerPaddingXS()
    {
        return $this->containerPaddingXS;
    }

    /**
     * @param string $containerPaddingXS
     * @return $this
     */
    public function setContainerPaddingXS($containerPaddingXS)
    {
        $this->containerPaddingXS = $containerPaddingXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerWidthXS()
    {
        return $this->containerWidthXS;
    }

    /**
     * @param string $containerWidthXS
     * @return $this
     */
    public function setContainerWidthXS($containerWidthXS)
    {
        $this->containerWidthXS = $containerWidthXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getTextAlignXS()
    {
        return $this->textAlignXS;
    }

    /**
     * @param string $textAlignXS
     * @return $this
     */
    public function setTextAlignXS($textAlignXS)
    {
        $this->textAlignXS = $textAlignXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundXS()
    {
        return $this->containerBackgroundXS;
    }

    /**
     * @param string $containerBackgroundXS
     * @return $this
     */
    public function setContainerBackgroundXS($containerBackgroundXS)
    {
        $this->containerBackgroundXS = $containerBackgroundXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundTypeXS()
    {
        return $this->containerBackgroundTypeXS;
    }

    /**
     * @param string $containerBackgroundTypeXS
     * @return $this
     */
    public function setContainerBackgroundTypeXS($containerBackgroundTypeXS)
    {
        $this->containerBackgroundTypeXS = $containerBackgroundTypeXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundRepeatXS()
    {
        return $this->containerBackgroundRepeatXS;
    }

    /**
     * @param string $containerBackgroundRepeatXS
     * @return $this
     */
    public function setContainerBackgroundRepeatXS($containerBackgroundRepeatXS)
    {
        $this->containerBackgroundRepeatXS = $containerBackgroundRepeatXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundPositionXS()
    {
        return $this->containerBackgroundPositionXS;
    }

    /**
     * @param string $containerBackgroundPositionXS
     * @return $this
     */
    public function setContainerBackgroundPositionXS($containerBackgroundPositionXS)
    {
        $this->containerBackgroundPositionXS = $containerBackgroundPositionXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundSizeXS()
    {
        return $this->containerBackgroundSizeXS;
    }

    /**
     * @param string $containerBackgroundSizeXS
     * @return $this
     */
    public function setContainerBackgroundSizeXS($containerBackgroundSizeXS)
    {
        $this->containerBackgroundSizeXS = $containerBackgroundSizeXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundColorXS()
    {
        return $this->containerBackgroundColorXS;
    }

    /**
     * @param string $containerBackgroundColorXS
     * @return $this
     */
    public function setContainerBackgroundColorXS($containerBackgroundColorXS)
    {
        $this->containerBackgroundColorXS = $containerBackgroundColorXS;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundImageXS()
    {
        return $this->containerBackgroundImageXS;
    }

    /**
     * Set image
     * @param string|Media $image
     * @return $this
     */
    public function setContainerBackgroundImageXS(Media $image = null)
    {
        $this->containerBackgroundImageXS = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundOverlayXS()
    {
        return $this->containerBackgroundOverlayXS;
    }

    /**
     * @param string $containerBackgroundOverlayXS
     * @return $this
     */
    public function setContainerBackgroundOverlayXS($containerBackgroundOverlayXS)
    {
        $this->containerBackgroundOverlayXS = $containerBackgroundOverlayXS;
        return $this;
    }

}