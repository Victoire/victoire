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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\InvalidArgumentException;

/**
 * Extension for Form collections.
 *
 * @author phiamo <phiamo@googlemail.com>
 */
class WidgetCollectionFormTypeExtension extends AbstractTypeExtension
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
        if (in_array('collection', $view->vars['block_prefixes'])) {
            if ($options['vic_widget_add_btn'] != null && !is_array($options['vic_widget_add_btn'])) {
                throw new InvalidArgumentException('The "vic_widget_add_btn" option must be an "array".');
            }

            if ((isset($options['allow_add']) && true === $options['allow_add']) && $options['vic_widget_add_btn']) {
                if (isset($options['vic_widget_add_btn']['attr']) && !is_array($options['vic_widget_add_btn']['attr'])) {
                    throw new InvalidArgumentException('The "vic_widget_add_btn.attr" option must be an "array".');
                }
                $options['vic_widget_add_btn'] = array_replace_recursive($this->options['vic_widget_add_btn'], $options['vic_widget_add_btn']);
            }
        }

        if ($view->parent && in_array('collection', $view->parent->vars['block_prefixes'])) {
            if ($options['vic_widget_remove_btn'] != null && !is_array($options['vic_widget_remove_btn'])) {
                throw new InvalidArgumentException('The "vic_widget_remove_btn" option must be an "array".');
            }

            if ((isset($options['allow_delete']) && true === $options['allow_delete']) && $options['vic_widget_remove_btn']) {
                if (isset($options['vic_widget_remove_btn']) && !is_array($options['vic_widget_remove_btn'])) {
                    throw new InvalidArgumentException('The "vic_widget_remove_btn" option must be an "array".');
                }
                $options['vic_widget_remove_btn'] = array_replace_recursive($this->options['vic_widget_remove_btn'], $options['vic_widget_remove_btn']);
            }
        }

        $view->vars['vic_omit_collection_item'] = $options['vic_omit_collection_item'];
        $view->vars['vic_widget_add_btn'] = $options['vic_widget_add_btn'];
        $view->vars['vic_widget_remove_btn'] = $options['vic_widget_remove_btn'];
        $view->vars['vic_prototype_names'] = $options['vic_prototype_names'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'vic_omit_collection_item' => true === $this->options['vic_render_collection_item'] ? false : true,
            'vic_widget_add_btn' => $this->options['vic_widget_add_btn'],
            'vic_widget_remove_btn' => $this->options['vic_widget_remove_btn'],
            'vic_prototype_names' => array()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
