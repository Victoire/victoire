<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

/*
 * This file is part of the MopaBootstrapBundle.
 *
 * (c) Philipp A. Mohrenweiser <phiamo@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for FormTab handling.
 *
 * @author phiamo <phiamo@googlemail.com>
 */
class TabType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'icon'       => null,
            'error_icon' => 'remove-sign',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['valid'] = $valid = !$form->isSubmitted() || $form->isValid();
        $view->vars['icon'] = $valid ? $options['icon'] : $options['error_icon'];
        $view->vars['tab_active'] = false;

        $view->parent->vars['vic_tabbed'] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tab';
    }
}
