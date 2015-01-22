<?php
namespace Victoire\Bundle\WidgetBundle\Entity\Traits;

/**
 * Layout trait adds fields to place a widget in its container
 */
trait LayoutTrait
{
    public static $tags = array(
        "section",
        "header",
        "footer",
        "nav",
        "article",
        "aside",
        "div",
    );

    /**
     * @var string
     *
     * @ORM\Column(name="container_tag", type="string", length=255, options={"default" = "div"})
     */
    protected $containerTag = "div";

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
     * @ORM\Column(name="container_margin_sm", type="string", length=255, nullable=true)
     */
    protected $containerMarginSM;

    /**
     * @var string
     *
     * @ORM\Column(name="container_padding_sm", type="string", length=255, nullable=true)
     */
    protected $containerPaddingSM;

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
     * @ORM\Column(name="container_background", type="string", length=255, nullable=true)
     */
    protected $containerBackground;

    /**
     * Set containerClass
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
     * Get containerClass
     *
     * @return string
     */
    public function getContainerClass()
    {
        return $this->containerClass;
    }

    /**
     * Set containerTag
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
     * Get containerTag
     *
     * @return string
     */
    public function getContainerTag()
    {
        return $this->containerTag;
    }

    /**
     * Set containerWidth
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
     * Get containerWidth
     *
     * @return string
     */
    public function getContainerWidth()
    {
        return $this->containerWidth;
    }

    /**
     * Set containerMargin
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
     * Get containerMargin
     *
     * @return string
     */
    public function getContainerMargin()
    {
        return $this->containerMargin;
    }

    /**
     * Set containerPadding
     * @param string $containerPadding
     *
     * @return WidgetLayout
     */
    public function setContainerPadding($containerPadding)
    {
        $this->containerPadding = $containerPadding;

        return $this;
    }

    /**
     * Get containerMarginXS
     *
     * @return string
     */
    public function getContainerMarginXS()
    {
        return $this->containerMarginXS;
    }

    /**
     * Set containerMarginXS
     * @param string $containerMarginXS
     *
     * @return $this
     */
    public function setContainerMarginXS($containerMarginXS)
    {
        $this->containerMarginXS = $containerMarginXS;

        return $this;
    }

    /**
     * Get containerPaddingXS
     *
     * @return string
     */
    public function getContainerPaddingXS()
    {
        return $this->containerPaddingXS;
    }

    /**
     * Set containerPaddingXS
     * @param string $containerPaddingXS
     *
     * @return $this
     */
    public function setContainerPaddingXS($containerPaddingXS)
    {
        $this->containerPaddingXS = $containerPaddingXS;

        return $this;
    }

    /**
     * Get containerMarginSM
     *
     * @return string
     */
    public function getContainerMarginSM()
    {
        return $this->containerMarginSM;
    }

    /**
     * Set containerMarginSM
     * @param string $containerMarginSM
     *
     * @return $this
     */
    public function setContainerMarginSM($containerMarginSM)
    {
        $this->containerMarginSM = $containerMarginSM;

        return $this;
    }

    /**
     * Get containerPaddingSM
     *
     * @return string
     */
    public function getContainerPaddingSM()
    {
        return $this->containerPaddingSM;
    }

    /**
     * Set containerPaddingSM
     * @param string $containerPaddingSM
     *
     * @return $this
     */
    public function setContainerPaddingSM($containerPaddingSM)
    {
        $this->containerPaddingSM = $containerPaddingSM;

        return $this;
    }

    /**
     * Get containerMarginMD
     *
     * @return string
     */
    public function getContainerMarginMD()
    {
        return $this->containerMarginMD;
    }

    /**
     * Set containerMarginMD
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
     * Get containerPaddingMD
     *
     * @return string
     */
    public function getContainerPaddingMD()
    {
        return $this->containerPaddingMD;
    }

    /**
     * Set containerPaddingMD
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
     * Get containerMarginLG
     *
     * @return string
     */
    public function getContainerMarginLG()
    {
        return $this->containerMarginLG;
    }

    /**
     * Set containerMarginLG
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
     * Get containerPaddingLG
     *
     * @return string
     */
    public function getContainerPaddingLG()
    {
        return $this->containerPaddingLG;
    }

    /**
     * Set containerPaddingLG
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
     * Get containerPadding
     *
     * @return string
     */
    public function getContainerPadding()
    {
        return $this->containerPadding;
    }

    /**
     * Get containerBackground
     *
     * @return string
     */
    public function getContainerBackground()
    {
        return $this->containerBackground;
    }

    /**
     * Set containerBackground
     * @param string $containerBackground
     *
     * @return $this
     */
    public function setContainerBackground($containerBackground)
    {
        $this->containerBackground = $containerBackground;

        return $this;
    }
}
