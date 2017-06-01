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

/**
 * Twig extension for form.
 *
 * Adds form_help and form_tabs functions.
 *
 * @author Paweł Madej (nysander) <pawel.madej@profarmaceuta.pl>
 * @author Charles Sanquer <charles.sanquer@gmail.com>
 */
class FormExtension extends \Twig_Extension
{
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('form_help', null, [
                'node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode',
                'is_safe'    => ['html'],
            ]),
            new \Twig_SimpleFunction('form_tabs', null, [
                'node_class' => 'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode',
                'is_safe'    => ['html'],
            ]),
        ];
    }
}
