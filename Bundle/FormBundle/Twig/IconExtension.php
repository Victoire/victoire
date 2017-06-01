<?php

namespace Victoire\Bundle\FormBundle\Twig;

/*
 * This file is part of the MopaBootstrapBundle.
 *
 * (c) Philipp A. Mohrenweiser <phiamo@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Response;

/**
 * MopaBootstrap Icon Extension.
 *
 * @author Craig Blanchette (isometriks) <craig.blanchette@gmail.com>
 */
class IconExtension extends \Twig_Extension implements \Twig_Extension_InitRuntimeInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var string
     */
    protected $iconSet;

    /**
     * @var string
     */
    protected $shortcut;

    /**
     * @var string
     */
    protected $iconTemplate;

    /**
     * Constructor.
     *
     * @param string $iconSet
     * @param string $shortcut
     */
    public function __construct($iconSet, $shortcut = null)
    {
        $this->iconSet = $iconSet;
        $this->shortcut = $shortcut;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $functions = [
            new \Twig_SimpleFunction('victoire_icon', [$this, 'renderIcon'], ['is_safe' => ['html']]),
        ];

        if ($this->shortcut) {
            $functions[] = new \Twig_SimpleFunction($this->shortcut, [$this, 'renderIcon'], ['is_safe' => ['html']]);
        }

        return $functions;
    }

    /**
     * Renders the icon.
     *
     * @param string $icon
     * @param bool   $inverted
     *
     * @return Response
     */
    public function renderIcon($icon, $inverted = false)
    {
        $template = $this->getIconTemplate();
        $context = [
            'icon'     => $icon,
            'inverted' => $inverted,
        ];

        return $template->renderBlock($this->iconSet, $context);
    }

    /**
     * @return \Twig_TemplateInterface
     */
    protected function getIconTemplate()
    {
        if ($this->iconTemplate === null) {
            $this->iconTemplate = $this->environment->loadTemplate('VictoireFormBundle::icons.html.twig');
        }

        return $this->iconTemplate;
    }
}
