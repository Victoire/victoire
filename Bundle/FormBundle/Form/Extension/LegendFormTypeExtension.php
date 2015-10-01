<?php

namespace Victoire\Bundle\FormBundle\Form\Extension;

/*
 * This file is part of the MopaBootstrapBundle.
 *
 * (c) Philipp A. Mohrenweiser <phiamo@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Extension for Form Legend handling.
 *
 * @author phiamo <phiamo@googlemail.com>
 */
class LegendFormTypeExtension extends AbstractTypeExtension
{
    private $renderFieldset;
    private $showLegend;
    private $showChildLegend;
    private $legendTag;
    private $renderRequiredAsterisk;
    private $renderOptionalText;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->renderFieldset = $options['vic_render_fieldset'];
        $this->showLegend = $options['vic_show_legend'];
        $this->showChildLegend = $options['vic_show_child_legend'];
        $this->legendTag = $options['vic_legend_tag'];
        $this->renderRequiredAsterisk = $options['vic_render_required_asterisk'];
        $this->renderOptionalText = $options['vic_render_optional_text'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['vic_render_fieldset'] = $options['vic_render_fieldset'];
        $view->vars['vic_show_legend'] = $options['vic_show_legend'];
        $view->vars['vic_show_child_legend'] = $options['vic_show_child_legend'];
        $view->vars['vic_legend_tag'] = $options['vic_legend_tag'];
        $view->vars['vic_label_render'] = $options['vic_label_render'];
        $view->vars['vic_render_required_asterisk'] = $options['vic_render_required_asterisk'];
        $view->vars['vic_render_optional_text'] = $options['vic_render_optional_text'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'vic_render_fieldset'          => $this->renderFieldset,
            'vic_show_legend'              => $this->showLegend,
            'vic_show_child_legend'        => $this->showChildLegend,
            'vic_legend_tag'               => $this->legendTag,
            'vic_label_render'             => true,
            'vic_render_required_asterisk' => $this->renderRequiredAsterisk,
            'vic_render_optional_text'     => $this->renderOptionalText,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
