<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\DataTransformer\JsonToArrayTransformer;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;

/**
 * Type for Victoire Link.
 */
class LinkType extends AbstractType
{
    private $analytics;
    private $viewCacheHelper;

    public function __construct($analytics, ViewCacheHelper $viewCacheHelper)
    {
        $this->analytics = $analytics;
        $this->viewCacheHelper = $viewCacheHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new JsonToArrayTransformer();
        $builder
            ->add('linkType', 'choice', [
                'label'       => 'form.link_type.linkType.label',
                'required'    => true,
                'choices'     => $options['linkTypeChoices'],
                'attr'        => [
                    'data-role' => 'vic-linkType-select',
                    'onchange'  => 'showSelectedLinkType($vic(this));',
                ],
            ])
            ->add('url', null, [
                'label'                          => 'form.link_type.url.label',
                'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group vic-hidden url-type'],
                'required'                       => true,
                'attr'                           => ['novalidate' => 'novalidate', 'placeholder' => 'form.link_type.url.placeholder'],
            ]);

        $rawPages = $this->viewCacheHelper->getAllViewsReferences();
        $pages = [];
        foreach ($rawPages as $page) {
            $pages[$page['id']] = $page['name'];
        }

        $builder->add('viewReference', 'choice', [
            'label'                          => 'form.link_type.view_reference.label',
            'required'                       => true,
            'attr'                           => ['novalidate' => 'novalidate'],
            'empty_value'                    => 'form.link_type.view_reference.blank',
            'choices'                        => $pages,
            'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group vic-hidden viewReference-type'],
        ])
        ->add('attachedWidget', 'entity', [
            'label'                          => 'form.link_type.attachedWidget.label',
            'empty_value'                    => 'form.link_type.attachedWidget.blank',
            'class'                          => 'VictoireWidgetBundle:Widget',
            'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group vic-hidden attachedWidget-type'],
            'required'                       => true,
            'attr'                           => ['novalidate' => 'novalidate'],
        ])
        ->add('route', null, [
            'label'                          => 'form.link_type.route.label',
            'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group vic-hidden route-type'],
            'required'                       => true,
            'attr'                           => ['novalidate' => 'novalidate', 'placeholder' => 'form.link_type.route.placeholder'],
        ])
        ->add($builder->create('route_parameters', 'text', [
                'label'                          => 'form.link_type.route_parameters.label',
                'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group vic-hidden route-type'],
                'required'                       => true,
                'attr'                           => ['novalidate' => 'novalidate', 'placeholder' => 'form.link_type.route_parameters.placeholder'],
            ])->addModelTransformer($transformer)
        )

        ->add('target', 'choice', [
            'label'     => 'form.link_type.target.label',
            'required'  => true,
            'choices'   => [
                '_parent'    => 'form.link_type.choice.target.parent',
                '_blank'     => 'form.link_type.choice.target.blank',
                'ajax-modal' => 'form.link_type.choice.target.ajax-modal',
            ],
            'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group viewReference-type page-type url-type route-type attachedWidget-type'],
        ]);

        if (!empty($this->analytics['google']) && $this->analytics['google']['enabled']) {
            $builder->add('analytics_track_code', null, [
                'label'          => 'form.link_type.analytics_track_code.label',
                'required'       => false,
                'attr'           => [
                    'placeholder' => 'form.link_type.analytics_track_code.placeholder',
                ],
                'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group analytics-type'],
            ]);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'          => 'Victoire\Bundle\CoreBundle\Entity\Link',
            'translation_domain'  => 'victoire',
            'horizontal'          => false,
            'linkTypeChoices'     => $this->getDefaultLinkTypeChoices(),
        ]);
    }

    /**
     * @return array
     */
    public static function getDefaultLinkTypeChoices()
    {
        return [
            'none'           => 'form.link_type.linkType.none',
            'viewReference'  => 'form.link_type.linkType.view_reference',
            'route'          => 'form.link_type.linkType.route',
            'url'            => 'form.link_type.linkType.url',
            'attachedWidget' => 'form.link_type.linkType.widget',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['horizontal'] = $options['horizontal'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'victoire_link';
    }
}
