<?php

namespace Victoire\Bundle\MediaBundle\Twig;

/**
 * MenuTwigExtension.
 */
class SvgTwigExtension extends \Twig_Extension
{
    protected $kernelRootDir;

    /**
     * SvgTwigExtension constructor.
     *
     * @param $kernelRootDir
     */
    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('include_svg', [$this, 'includeSvg'], ['is_safe' => ['html']]),
        ];
    }

    public function includeSvg($url)
    {
        return file_get_contents($this->kernelRootDir.'/../web'.$url);
    }

    public function getName()
    {
        return 'victoire_media_svg_extension';
    }
}
