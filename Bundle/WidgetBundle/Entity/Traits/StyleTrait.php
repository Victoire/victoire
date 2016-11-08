<?php

namespace Victoire\Bundle\WidgetBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\MediaBundle\Entity\Media;
use Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTraits\StyleLGTrait;
use Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTraits\StyleMDTrait;
use Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTraits\StyleSMTrait;
use Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTraits\StyleXSTrait;

/**
 * Style trait adds fields to place a widget in its container.
 */
trait StyleTrait
{
    /*******************        RESPONSIVE PROPERTIES         **********************/
    use StyleXSTrait;
    use StyleSMTrait;
    use StyleMDTrait;
    use StyleLGTrait;

    /*******************  GLOBAL PROPERTIES (NON RESPONSIVE)  **********************/
    public static $tags = [
        'section',
        'header',
        'footer',
        'nav',
        'article',
        'aside',
        'div',
    ];

    /**
     * @var string
     *
     * @ORM\Column(name="container_tag", type="string", length=255, options={"default" = "div"})
     */
    protected $containerTag = 'div';

    /**
     * @var string
     *
     * @ORM\Column(name="container_class", type="string", length=255, nullable=true)
     */
    protected $containerClass;

    /**
     * @var string
     *
     * @ORM\Column(name="container_width", type="string", length=255, nullable=true)
     */
    protected $containerWidth;

    /**
     * @var string
     *
     * @ORM\Column(name="container_height", type="string", length=255, nullable=true)
     */
    protected $containerHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="container_margin", type="string", length=255, nullable=true)
     */
    protected $containerMargin;

    /**
     * @var string
     *
     * @ORM\Column(name="container_padding", type="string", length=255, nullable=true)
     */
    protected $containerPadding;

    /**
     * @var string
     *
     * @ORM\Column(name="text_align", type="string", length=15, nullable=true)
     */
    protected $textAlign;

    /**
     * @var string
     *
     * @deprecated
     * @ORM\Column(name="container_background", type="string", length=255, nullable=true)
     */
    protected $containerBackground;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_type", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundType;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_repeat", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundRepeat;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_position", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundPosition;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_size", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundSize;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_color", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundColor;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="container_background_image_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $containerBackgroundImage;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_overlay", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundOverlay;

    /**
     * Set containerTag.
     *
     * @param string $containerTag
     *
     * @return $this
     */
    public function setContainerTag($containerTag)
    {
        $this->containerTag = $containerTag;

        return $this;
    }

    /**
     * Get containerTag.
     *
     * @return string
     */
    public function getContainerTag()
    {
        return $this->containerTag;
    }

    /**
     * Set containerClass.
     *
     * @param string $containerClass
     *
     * @return $this
     */
    public function setContainerClass($containerClass)
    {
        $this->containerClass = $containerClass;

        return $this;
    }

    /**
     * Get containerClass.
     *
     * @return string
     */
    public function getContainerClass()
    {
        return $this->containerClass;
    }

    /**
     * Set containerWidth.
     *
     * @param string $containerWidth
     *
     * @return $this
     */
    public function setContainerWidth($containerWidth)
    {
        $this->containerWidth = $containerWidth;

        return $this;
    }

    /**
     * Get containerWidth.
     *
     * @return string
     */
    public function getContainerWidth()
    {
        return $this->containerWidth;
    }

    /**
     * Set containerHeight.
     *
     * @param string $containerHeight
     *
     * @return $this
     */
    public function setContainerHeight($containerHeight)
    {
        $this->containerHeight = $containerHeight;

        return $this;
    }

    /**
     * Get containerHeight.
     *
     * @return string
     */
    public function getContainerHeight()
    {
        return $this->containerHeight;
    }

    /**
     * Set containerMargin.
     *
     * @param string $containerMargin
     *
     * @return $this
     */
    public function setContainerMargin($containerMargin)
    {
        $this->containerMargin = $containerMargin;

        return $this;
    }

    /**
     * Get containerMargin.
     *
     * @return string
     */
    public function getContainerMargin()
    {
        return $this->containerMargin;
    }

    /**
     * Set containerPadding.
     *
     * @param string $containerPadding
     *
     * @return $this
     */
    public function setContainerPadding($containerPadding)
    {
        $this->containerPadding = $containerPadding;

        return $this;
    }

    /**
     * Get containerPadding.
     *
     * @return string
     */
    public function getContainerPadding()
    {
        return $this->containerPadding;
    }

    /**
     * Get textAlign.
     *
     * @return string
     */
    public function getTextAlign()
    {
        return $this->textAlign;
    }

    /**
     * Set textAlign.
     *
     * @param string $textAlign
     *
     * @return $this
     */
    public function setTextAlign($textAlign)
    {
        $this->textAlign = $textAlign;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackground()
    {
        return $this->containerBackground;
    }

    /**
     * @param string $containerBackground
     *
     * @return $this
     */
    public function setContainerBackground($containerBackground)
    {
        $this->containerBackground = $containerBackground;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundType()
    {
        return $this->containerBackgroundType;
    }

    /**
     * @param string $containerBackgroundType
     */
    public function setContainerBackgroundType($containerBackgroundType)
    {
        $this->containerBackgroundType = $containerBackgroundType;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundRepeat()
    {
        return $this->containerBackgroundRepeat;
    }

    /**
     * @param string $containerBackgroundRepeat
     *
     * @return $this
     */
    public function setContainerBackgroundRepeat($containerBackgroundRepeat)
    {
        $this->containerBackgroundRepeat = $containerBackgroundRepeat;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundPosition()
    {
        return $this->containerBackgroundPosition;
    }

    /**
     * @param string $containerBackgroundPosition
     *
     * @return $this
     */
    public function setContainerBackgroundPosition($containerBackgroundPosition)
    {
        $this->containerBackgroundPosition = $containerBackgroundPosition;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundSize()
    {
        return $this->containerBackgroundSize;
    }

    /**
     * @param string $containerBackgroundSize
     *
     * @return $this
     */
    public function setContainerBackgroundSize($containerBackgroundSize)
    {
        $this->containerBackgroundSize = $containerBackgroundSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundColor()
    {
        return $this->containerBackgroundColor;
    }

    /**
     * @param string $containerBackgroundColor
     *
     * @return $this
     */
    public function setContainerBackgroundColor($containerBackgroundColor)
    {
        $this->containerBackgroundColor = $containerBackgroundColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundImage()
    {
        return $this->containerBackgroundImage;
    }

    /**
     * Set image.
     *
     * @param string|Media $image
     *
     * @return $this
     */
    public function setContainerBackgroundImage(Media $image = null)
    {
        $this->containerBackgroundImage = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundOverlay()
    {
        return $this->containerBackgroundOverlay;
    }

    /**
     * @param string $containerBackgroundOverlay
     *
     * @return $this
     */
    public function setContainerBackgroundOverlay($containerBackgroundOverlay)
    {
        $this->containerBackgroundOverlay = $containerBackgroundOverlay;

        return $this;
    }
}
