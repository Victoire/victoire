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
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extension for Form Widget Bootstrap handling.
 *
 * @author phiamo <phiamo@googlemail.com>
 */
class WidgetFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (in_array('percent', $view->vars['block_prefixes']) && null === $options['vic_widget_addon_append']) {
            $options['vic_widget_addon_append'] = [];
        }

        if (in_array('money', $view->vars['block_prefixes']) && null === $options['vic_widget_addon_prepend']) {
            $options['vic_widget_addon_prepend'] = [];
        }

        $view->vars['vic_widget_form_control_class'] = $options['vic_widget_form_control_class'];
        $view->vars['vic_widget_form_group'] = $options['vic_widget_form_group'];
        $view->vars['vic_widget_addon_prepend'] = $options['vic_widget_addon_prepend'];
        $view->vars['vic_widget_addon_append'] = $options['vic_widget_addon_append'];
        $view->vars['vic_widget_prefix'] = $options['vic_widget_prefix'];
        $view->vars['vic_widget_suffix'] = $options['vic_widget_suffix'];
        $view->vars['vic_widget_type'] = $options['vic_widget_type'];
        $view->vars['vic_widget_items_attr'] = $options['vic_widget_items_attr'];
        $view->vars['vic_vic_widget_form_group_attr'] = $options['vic_vic_widget_form_group_attr'];
        $view->vars['vic_widget_vic_checkbox_label'] = $options['vic_widget_vic_checkbox_label'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'vic_widget_form_control_class'  => 'vic-form-control',
            'vic_widget_form_group'          => true,
            'vic_widget_addon_prepend'       => null,
            'vic_widget_addon_append'        => null,
            'vic_widget_prefix'              => null,
            'vic_widget_suffix'              => null,
            'vic_widget_type'                => '',
            'vic_widget_items_attr'          => [],
            'vic_vic_widget_form_group_attr' => [
                'class' => 'vic-form-group',
            ],
            'vic_widget_vic_checkbox_label' => $this->options['vic_checkbox_label'],
        ]);
        $resolver->setAllowedValues([
            'vic_widget_type' => [
                'inline',
                '',
            ],
            'vic_widget_vic_checkbox_label' => [
                'label',
                'widget',
                'both',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
