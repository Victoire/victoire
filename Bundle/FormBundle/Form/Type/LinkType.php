<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\Entity\Link;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;

/**
 * Type for Victoire Link.
 */
class LinkType extends AbstractType
{
    protected $analytics;
    protected $viewReferenceRepository;
    protected $availableLocales;
    protected $requestStack;
    /**
     * @var array
     */
    private $modalLayouts;

    /**
     * LinkType constructor.
     *
     * @param array                   $analytics
     * @param ViewReferenceRepository $viewReferenceRepository
     * @param array                   $availableLocales
     * @param RequestStack            $requestStack
     * @param array                   $modalLayouts
     */
    public function __construct(
        array $analytics,
        ViewReferenceRepository $viewReferenceRepository,
        array $availableLocales,
        RequestStack $requestStack,
        array $modalLayouts
    ) {
        $this->analytics = $analytics;
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->availableLocales = $availableLocales;
        $this->requestStack = $requestStack;
        $this->modalLayouts = $modalLayouts;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('linkType', ChoiceType::class, [
                'label'             => 'form.link_type.linkType.label',
                'required'          => true,
                'choices'           => $options['linkTypeChoices'],
                'choices_as_values' => true,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder, $options) {
                /** @var Link $data */
                $data = $event->getData();
                $form = $event->getForm();
                self::manageLinkTypeRelatedFields($data ? $data->getLinkType() : Link::TYPE_NONE, $data ? $data->getLocale() : null, $form, $builder, $options);
                self::manageTargetRelatedFields($data ? $data->getTarget() : Link::TARGET_PARENT, $form, $options);
                self::manageRefreshTarget($form, $options);
            });

        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($builder, $options) {
                $form = $event->getForm();
                $data = $event->getData();
                $locale = isset($data['locale']) ? $data['locale'] : null;
                // manage conditional related linkType in pre submit (ajax call to refresh view)
                self::manageLinkTypeRelatedFields($data['linkType'], $locale, $form, $builder, $options);
                if (isset($data['target'])) {
                    self::manageTargetRelatedFields($data['target'], $form, $options);
                }
            });

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
     * By default set data-target to root Form.
     *
     * @param FormInterface $form
     * @param array         $options
     */
    protected function manageRefreshTarget($form, $options)
    {
        $rootFormName = $form->getRoot()->getName();
        $linkTypeConfig = $form->get('linkType')->getConfig();
        $linkTypeOptions = $linkTypeConfig->getOptions();

        $form->add(
            'linkType',
            $linkTypeConfig->getType()->getName(),
            array_replace(
                $linkTypeOptions,
                [
                    'attr' => [
                        'data-refreshOnChange' => 'true',
                        'data-target' => $options['refresh-target'] ?: 'form[name="' . $rootFormName . '"]',
                        'data-update-strategy' => 'replaceWith',
                    ],
                ]
            )
        );
    }

    /**
     * Add the types related to the LinkType value.
     *
     * @param                                    $linkType
     * @param                                    $locale
     * @param FormBuilderInterface|FormInterface $form
     * @param FormBuilderInterface               $builder
     * @param array                              $options
     */
    protected function manageLinkTypeRelatedFields($linkType, $locale, $form, FormBuilderInterface $builder, $options)
    {
        $form->remove('route');
        $form->remove('url');
        $form->remove('attachedWidget');
        $form->remove('viewReference');
        $form->remove('locale');
        $this->addTargetField($form, $options);
        switch ($linkType) {
            case Link::TYPE_VIEW_REFERENCE:
                $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();
                $form->add('viewReference', ChoiceType::class, [
                    'label'                          => 'form.link_type.view_reference.label',
                    'required'                       => true,
                    'attr'                           => ['novalidate' => 'novalidate'],
                    'placeholder'                    => 'form.link_type.view_reference.blank',
                    'choices'                        => $this->viewReferenceRepository->getChoices($locale),
                    'choices_as_values'              => true,
                    'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group'],
                ])->add('locale', ChoiceType::class, [
                    'label'       => 'form.link_type.locale.label',
                    'choices'     => array_combine($this->availableLocales, $this->availableLocales),
                    'attr'        => ['data-refreshOnChange' => 'true'],
                ]);
                break;
            case Link::TYPE_ROUTE:
                $form->add('route', null, [
                    'label'                          => 'form.link_type.route.label',
                    'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group'],
                    'required'                       => true,
                    'attr'                           => ['novalidate' => 'novalidate', 'placeholder' => 'form.link_type.route.placeholder'],
                ])
                    ->add('route_parameters', JsonType::class, [
                        'label'                          => 'form.link_type.route_parameters.label',
                        'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group'],
                        'required'                       => true,
                        'attr'                           => ['novalidate' => 'novalidate', 'placeholder' => 'form.link_type.route_parameters.placeholder'],
                    ]);
                break;
            case Link::TYPE_URL:
                $form->add('url', null, [
                    'label'                          => 'form.link_type.url.label',
                    'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group'],
                    'required'                       => true,
                    'attr'                           => ['novalidate' => 'novalidate', 'placeholder' => 'form.link_type.url.placeholder'],
                ]);
                break;
            case Link::TYPE_WIDGET:
                $form->add('attachedWidget', EntityType::class, [
                    'label'                          => 'form.link_type.attachedWidget.label',
                    'placeholder'                    => 'form.link_type.attachedWidget.blank',
                    'class'                          => 'VictoireWidgetBundle:Widget',
                    'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group'],
                    'required'                       => true,
                    'attr'                           => ['novalidate' => 'novalidate'],
                ]);
                break;
            case Link::TYPE_NONE:
            case null:
                $form->remove('target');
                break;
        }
    }

    /**
     * Add the types related to the target value.
     *
     * @param string                             $target
     * @param FormBuilderInterface|FormInterface $form
     * @param array                              $options
     */
    protected function manageTargetRelatedFields($target, $form, $options)
    {
        if ($target == Link::TARGET_MODAL && count($this->modalLayouts) > 1) {
            $form->add('modalLayout', ChoiceType::class, [
                'label'        => 'form.link_type.target.modalLayouts.label',
                'required'     => true,
                'choices'      => $this->modalLayouts,
                'choice_label' => function ($value, $key, $index) {
                    return 'form.link_type.target.modalLayouts.choices.'.$value;
                },
                'choices_as_values'              => true,
                'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group viewReference-type page-type url-type route-type attachedWidget-type'],
            ]);
        } else {
            $form->remove('modalLayout');
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
            'refresh-target'     => null,
        ]);
    }

    /**
     * @return array
     */
    public static function getDefaultLinkTypeChoices()
    {
        return [
            'form.link_type.linkType.none'           => Link::TYPE_NONE,
            'form.link_type.linkType.view_reference' => Link::TYPE_VIEW_REFERENCE,
            'form.link_type.linkType.route'          => Link::TYPE_ROUTE,
            'form.link_type.linkType.url'            => Link::TYPE_URL,
            'form.link_type.linkType.widget'         => Link::TYPE_WIDGET,
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
     * add the target Field.
     *
     * @param FormBuilderInterface|FormInterface $form
     * @param array                              $options
     */
    protected function addTargetField($form, array $options)
    {

        $rootFormName = $form->getRoot()->getName();
        $form->add('target', ChoiceType::class, [
            'label'    => 'form.link_type.target.label',
            'required' => true,
            'choices'  => [
                'form.link_type.choice.target.parent'     => Link::TARGET_PARENT,
                'form.link_type.choice.target.blank'      => Link::TARGET_BLANK,
                'form.link_type.choice.target.ajax-modal' => Link::TARGET_MODAL,
            ],
            'choices_as_values' => true,
            'attr'              => [
                'data-refreshOnChange' => 'true',
                'data-target' => $options['refresh-target'] ?: 'form[name="' . $rootFormName . '"]',
                'data-update-strategy' => 'replaceWith',
            ],
            'vic_vic_widget_form_group_attr' => ['class' => 'vic-form-group viewReference-type page-type url-type route-type attachedWidget-type'],
        ]);
    }
}
