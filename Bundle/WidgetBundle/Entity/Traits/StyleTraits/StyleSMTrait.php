<?php
namespace Victoire\Bundle\WidgetBundle\Entity\Traits\StyleTraits;

use Victoire\Bundle\MediaBundle\Entity\Media;

trait StyleSMTrait {

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
     * @ORM\Column(name="container_width_sm", type="string", length=255, nullable=true)
     */
    protected $containerWidthSM;

    /**
     * @var string
     *
     * @ORM\Column(name="text_align_sm", type="string", length=15, nullable=true)
     */
    protected $textAlignSM;

    /**
     * @var string
     * @deprecated
     * @ORM\Column(name="container_background_sm", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundSM;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_type_sm", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundTypeSM;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_repeat_sm", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundRepeatSM;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_position_sm", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundPositionSM;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_size_sm", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundSizeSM;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_color_sm", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundColorSM;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="container_background_image_sm_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $containerBackgroundImageSM;

    /**
     * @var string
     *
     * @ORM\Column(name="container_background_overlay_sm", type="string", length=255, nullable=true)
     */
    protected $containerBackgroundOverlaySM;

    /**
     * Delete all SM background
     */
    public function deleteBackgroundSM() {
        $this->containerBackgroundSM = null;
        $this->containerBackgroundTypeSM = null;
        $this->containerBackgroundColorSM = null;
        $this->containerBackgroundImageSM = null;
        $this->containerBackgroundRepeatSM = null;
        $this->containerBackgroundSizeSM = null;
        $this->containerBackgroundPositionSM = null;
        $this->containerBackgroundOverlaySM = null;
    }

    /**
     * @return string
     */
    public function getContainerMarginSM()
    {
        return $this->containerMarginSM;
    }

    /**
     * @param string $containerMarginSM
     * @return $this
     */
    public function setContainerMarginSM($containerMarginSM)
    {
        $this->containerMarginSM = $containerMarginSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerPaddingSM()
    {
        return $this->containerPaddingSM;
    }

    /**
     * @param string $containerPaddingSM
     * @return $this
     */
    public function setContainerPaddingSM($containerPaddingSM)
    {
        $this->containerPaddingSM = $containerPaddingSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerWidthSM()
    {
        return $this->containerWidthSM;
    }

    /**
     * @param string $containerWidthSM
     * @return $this
     */
    public function setContainerWidthSM($containerWidthSM)
    {
        $this->containerWidthSM = $containerWidthSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getTextAlignSM()
    {
        return $this->textAlignSM;
    }

    /**
     * @param string $textAlignSM
     * @return $this
     */
    public function setTextAlignSM($textAlignSM)
    {
        $this->textAlignSM = $textAlignSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundSM()
    {
        return $this->containerBackgroundSM;
    }

    /**
     * @param string $containerBackgroundSM
     * @return $this
     */
    public function setContainerBackgroundSM($containerBackgroundSM)
    {
        $this->containerBackgroundSM = $containerBackgroundSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundTypeSM()
    {
        return $this->containerBackgroundTypeSM;
    }

    /**
     * @param string $containerBackgroundTypeSM
     * @return $this
     */
    public function setContainerBackgroundTypeSM($containerBackgroundTypeSM)
    {
        $this->containerBackgroundTypeSM = $containerBackgroundTypeSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundRepeatSM()
    {
        return $this->containerBackgroundRepeatSM;
    }

    /**
     * @param string $containerBackgroundRepeatSM
     * @return $this
     */
    public function setContainerBackgroundRepeatSM($containerBackgroundRepeatSM)
    {
        $this->containerBackgroundRepeatSM = $containerBackgroundRepeatSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundPositionSM()
    {
        return $this->containerBackgroundPositionSM;
    }

    /**
     * @param string $containerBackgroundPositionSM
     * @return $this
     */
    public function setContainerBackgroundPositionSM($containerBackgroundPositionSM)
    {
        $this->containerBackgroundPositionSM = $containerBackgroundPositionSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundSizeSM()
    {
        return $this->containerBackgroundSizeSM;
    }

    /**
     * @param string $containerBackgroundSizeSM
     * @return $this
     */
    public function setContainerBackgroundSizeSM($containerBackgroundSizeSM)
    {
        $this->containerBackgroundSizeSM = $containerBackgroundSizeSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundColorSM()
    {
        return $this->containerBackgroundColorSM;
    }

    /**
     * @param string $containerBackgroundColorSM
     * @return $this
     */
    public function setContainerBackgroundColorSM($containerBackgroundColorSM)
    {
        $this->containerBackgroundColorSM = $containerBackgroundColorSM;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundImageSM()
    {
        return $this->containerBackgroundImageSM;
    }

    /**
     * Set image
     * @param string|Media $image
     * @return $this
     */
    public function setContainerBackgroundImageSM(Media $image = null)
    {
        $this->containerBackgroundImageSM = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerBackgroundOverlaySM()
    {
        return $this->containerBackgroundOverlaySM;
    }

    /**
     * @param string $containerBackgroundOverlaySM
     * @return $this
     */
    public function setContainerBackgroundOverlaySM($containerBackgroundOverlaySM)
    {
        $this->containerBackgroundOverlaySM = $containerBackgroundOverlaySM;
        return $this;
    }

}