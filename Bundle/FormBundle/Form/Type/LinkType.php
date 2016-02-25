<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\DataTransformer\JsonToArrayTransformer;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;

/**
 * Type for Victoire Link.
 */
class LinkType extends AbstractType
{
    private $analytics;
    private $viewReferenceRepository;

    /**
     * LinkType constructor.
     *
     * @param $analytics
     * @param ViewReferenceRepository $viewReferenceRepository
     */
    public function __construct($analytics, ViewReferenceRepository $viewReferenceRepository)
    {
        $this->analytics = $analytics;
        $this->viewReferenceRepository = $viewReferenceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new JsonToArrayTransformer();
        $builder
            ->add('linkType', ChoiceType::class, [
                'label'             => 'form.link_type.linkType.label',
                'required'          => true,
                'choices'           => $options['linkTypeChoices'],
                'choices_as_values' => true,
                'attr'              => [
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

        $builder->add('viewReference', ChoiceType::class, [
            'label'                          => 'form.link_type.view_reference.label',
            'required'                       => true,
            'attr'                           => ['novalidate' => 'novalidate'],
            'placeholder'                    => 'form.link_type.view_reference.blank',
            'choices'                        => $this->viewReferenceRepository->getChoices(),
            'choices_as_values'              => true,
            'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group vic-hidden viewReference-type'],
        ])
        ->add('attachedWidget', EntityType::class, [
            'label'                          => 'form.link_type.attachedWidget.label',
            'placeholder'                    => 'form.link_type.attachedWidget.blank',
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
        ->add($builder->create('route_parameters', TextType::class, [
                'label'                          => 'form.link_type.route_parameters.label',
                'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group vic-hidden route-type'],
                'required'                       => true,
                'attr'                           => ['novalidate' => 'novalidate', 'placeholder' => 'form.link_type.route_parameters.placeholder'],
            ])->addModelTransformer($transformer)
        )

        ->add('target', ChoiceType::class, [
            'label'    => 'form.link_type.target.label',
            'required' => true,
            'choices'  => [
                'form.link_type.choice.target.parent'     => '_parent',
                'form.link_type.choice.target.blank'      => '_blank',
                'form.link_type.choice.target.ajax-modal' => 'ajax-modal',
            ],
            'choices_as_values'              => true,
            'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group viewReference-type page-type url-type route-type attachedWidget-type'],
        ]);

        if (!empty($this->analytics['google']) && $this->analytics['google']['enabled']) {
            $builder->add('analytics_track_code', null, [
                'label'    => 'form.link_type.analytics_track_code.label',
                'required' => false,
                'attr'     => [
                    'placeholder' => 'form.link_type.analytics_track_code.placeholder',
                ],
                'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group analytics-type'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\CoreBundle\Entity\Link',
            'translation_domain' => 'victoire',
            'horizontal'         => false,
            'linkTypeChoices'    => $this->getDefaultLinkTypeChoices(),
        ]);
    }

    /**
     * @return array
     */
    public static function getDefaultLinkTypeChoices()
    {
        return [
            'form.link_type.linkType.none'           => 'none',
            'form.link_type.linkType.view_reference' => 'viewReference',
            'form.link_type.linkType.route'          => 'route',
            'form.link_type.linkType.url'            => 'url',
            'form.link_type.linkType.widget'         => 'attachedWidget',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['horizontal'] = $options['horizontal'];
    }
}
