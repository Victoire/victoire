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
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extension for Help Forms handling.
 *
 * @author phiamo <phiamo@googlemail.com>
 */
class HelpFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * {@inheritdoc}
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
        $view->vars['vic_help_block'] = $options['vic_help_block'];
        $view->vars['vic_help_label'] = $options['vic_help_label'];

        if (null !== $options['vic_help_label_tooltip'] && !is_array($options['vic_help_label_tooltip'])) {
            throw new InvalidArgumentException('The "vic_help_label_tooltip" option must be an "array".');
        }

        if ($options['vic_help_label_tooltip']) {
            if (!isset($options['vic_help_label_tooltip']['title'])) {
                $options['vic_help_label_tooltip']['title'] = $this->options['vic_help_label_tooltip']['title'];
            }
            if (!isset($options['vic_help_label_tooltip']['text'])) {
                $options['vic_help_label_tooltip']['text'] = $this->options['vic_help_label_tooltip']['text'];
            }
            if (!isset($options['vic_help_label_tooltip']['icon'])) {
                $options['vic_help_label_tooltip']['icon'] = $this->options['vic_help_label_tooltip']['icon'];
            }
            if (!isset($options['vic_help_label_tooltip']['placement'])) {
                $options['vic_help_label_tooltip']['placement'] = $this->options['vic_help_label_tooltip']['placement'];
            }
        }

        if (null !== $options['vic_help_label_popover'] && !is_array($options['vic_help_label_popover'])) {
            throw new InvalidArgumentException('The "vic_help_label_popover" option must be an "array".');
        }

        if ($options['vic_help_label_popover']) {
            if (!isset($options['vic_help_label_popover']['title'])) {
                $options['vic_help_label_popover']['title'] = $this->options['vic_help_label_popover']['title'];
            }
            if (!isset($options['vic_help_label_popover']['text'])) {
                $options['vic_help_label_popover']['text'] = $this->options['vic_help_label_popover']['text'];
            }
            if (!isset($options['vic_help_label_popover']['content'])) {
                $options['vic_help_label_popover']['content'] = $this->options['vic_help_label_popover']['content'];
            }
            if (!isset($options['vic_help_label_popover']['icon'])) {
                $options['vic_help_label_popover']['icon'] = $this->options['vic_help_label_popover']['icon'];
            }
            if (!isset($options['vic_help_label_popover']['placement'])) {
                $options['vic_help_label_popover']['placement'] = $this->options['vic_help_label_popover']['placement'];
            }
        }

        if (null !== $options['vic_help_widget_popover'] && !is_array($options['vic_help_widget_popover'])) {
            throw new InvalidArgumentException('The "vic_help_widget_popover" option must be an "array".');
        }

        if ($options['vic_help_widget_popover']) {
            if (!isset($options['vic_help_widget_popover']['title'])) {
                $options['vic_help_widget_popover']['title'] = $this->options['vic_help_widget_popover']['title'];
            }
            if (!isset($options['vic_help_widget_popover']['content'])) {
                $options['vic_help_widget_popover']['content'] = $this->options['vic_help_widget_popover']['content'];
            }
            if (!isset($options['vic_help_widget_popover']['toggle'])) {
                $options['vic_help_widget_popover']['toggle'] = $this->options['vic_help_widget_popover']['toggle'];
            }
            if (!isset($options['vic_help_widget_popover']['trigger'])) {
                $options['vic_help_widget_popover']['trigger'] = $this->options['vic_help_widget_popover']['trigger'];
            }
            if (!isset($options['vic_help_widget_popover']['placement'])) {
                $options['vic_help_widget_popover']['placement'] = $this->options['vic_help_widget_popover']['placement'];
            }
            if (!isset($options['vic_help_widget_popover']['selector'])) {
                $options['vic_help_widget_popover']['selector'] = $this->options['vic_help_widget_popover']['selector'];
            }
        }

        $view->vars['vic_help_label_tooltip'] = $options['vic_help_label_tooltip'];
        $view->vars['vic_help_label_popover'] = $options['vic_help_label_popover'];
        $view->vars['vic_help_widget_popover'] = $options['vic_help_widget_popover'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'vic_help_block'          => null,
            'vic_help_label'          => null,
            'vic_help_label_tooltip'  => $this->options['vic_help_label_tooltip'],
            'vic_help_label_popover'  => $this->options['vic_help_label_popover'],
            'vic_help_widget_popover' => $this->options['vic_help_widget_popover'],
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
