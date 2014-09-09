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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Widget\RenderBundle\DataTransformer\JsonToArrayTransformer;

/**
 * Type for FormTab handling.
 *
 * @author phiamo <phiamo@googlemail.com>
 */
class LinkType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new JsonToArrayTransformer();
        $builder->add('linkType', 'choice', array(
            'label'       => 'form.link_type.linkType.label',
            'required'    => true,
            'choices'     => array(
                'page'           => 'form.link_type.linkType.page',
                'route'          => 'form.link_type.linkType.route',
                'url'            => 'form.link_type.linkType.url',
                'attachedWidget' => 'form.link_type.linkType.widget'
            ),
            'attr'        => array(
                'class'    => 'vic-linkType-select',
                'onchange' => 'showSelectedLinkType($vic(this));',
            )
        ))
        ->add('url', null, array(
            'label'                          => 'form.link_type.url.label',
            'vic_vic_widget_form_group_attr' => array('class' => 'vic-form-group vic-hidden url-type'),
            'required'                       => true,
            'attr' => array('novalidate' => 'novalidate'),
        ))
        ->add('page', 'entity', array(
            'label'                          => 'form.link_type.page.label',
            'required'                       => true,
            'attr' => array('novalidate' => 'novalidate'),
            'empty_value'                    => 'form.link_type.page.blank',
            'class'                          => 'VictoirePageBundle:Page',
            'property'                       => 'name',
            'vic_vic_widget_form_group_attr' => array('class' => 'vic-form-group vic-hidden page-type'),
        ))
        ->add('attachedWidget', 'entity', array(
            'label'                          => 'form.link_type.attachedWidget.label',
            'empty_value'                    => 'form.link_type.attachedWidget.blank',
            'class'                          => 'VictoireWidgetBundle:Widget',
            'vic_vic_widget_form_group_attr' => array('class' => 'vic-form-group vic-hidden attachedWidget-type'),
            'required'                       => true,
            'attr' => array('novalidate' => 'novalidate'),
        ))
        ->add('route', null, array(
            'label'                          => 'form.link_type.route.label',
            'vic_vic_widget_form_group_attr' => array('class' => 'vic-form-group vic-hidden route-type'),
            'required'                       => true,
            'attr' => array('novalidate' => 'novalidate'),
        ))
        ->add($builder->create('route_parameters', 'text', array(
                'label'                          => 'form.link_type.route_parameters.label',
                'vic_vic_widget_form_group_attr' => array('class' => 'vic-form-group vic-hidden route-type'),
                'required'  => true,
                'attr' => array('novalidate' => 'novalidate'),
            ))->addModelTransformer($transformer)
        )

        ->add('target', 'choice', array(
            'label'     => 'form.link_type.target.label',
            'required'  => true,
            'choices'   => array(
                '_parent'    => 'form.link_type.choice.target.parent',
                '_blank'     => 'form.link_type.choice.target.blank',
                'ajax-modal' => 'form.link_type.choice.target.ajax-modal',
            ),
        ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true
        ));
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'victoire_link';
    }
}
