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
 * Extension for enabling Horizontal Forms.
 *
 * @author phiamo <phiamo@googlemail.com>
 */
class HorizontalFormTypeExtension extends AbstractTypeExtension
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
        $view->vars['vic_horizontal'] = $options['vic_horizontal'];
        $view->vars['vic_horizontal_label_class'] = $options['vic_horizontal_label_class'];
        $view->vars['vic_horizontal_label_offset_class'] = $options['vic_horizontal_label_offset_class'];
        $view->vars['vic_horizontal_input_wrapper_class'] = $options['vic_horizontal_input_wrapper_class'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'vic_horizontal'                     => $this->options['vic_horizontal'],
                'vic_horizontal_label_class'         => $this->options['vic_horizontal_label_class'],
                'vic_horizontal_label_offset_class'  => $this->options['vic_horizontal_label_offset_class'],
                'vic_horizontal_input_wrapper_class' => $this->options['vic_horizontal_input_wrapper_class'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
