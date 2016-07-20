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
     * LinkType constructor.
     *
     * @param array                   $analytics
     * @param ViewReferenceRepository $viewReferenceRepository
     * @param array                   $availableLocales
     * @param RequestStack            $requestStack
     */
    public function __construct($analytics, ViewReferenceRepository $viewReferenceRepository, $availableLocales, RequestStack $requestStack)
    {
        $this->analytics = $analytics;
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->availableLocales = $availableLocales;
        $this->requestStack = $requestStack;
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
                'attr'              => [
                    'data-refreshOnChange' => 'true',
                    'data-target'          => $options['refresh-target'],
                ],
            ])
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
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
                $data = $event->getData();
                $form = $event->getForm();
                if ($data) {
                    // manage conditional related status in preset data
                    self::manageLinkTypeRelatedFields($data->getLinkType(), $data->getLocale(), $form, $builder);
                } else {
                    $form->remove('target');
                }
            });

        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($builder) {
                $form = $event->getForm();
                $data = $event->getData();
                $locale = isset($data['locale']) ? $data['locale'] : null;
                // manage conditional related linkType in pre submit (ajax call to refresh view)
                self::manageLinkTypeRelatedFields($data['linkType'], $locale, $form, $builder);
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
     * Add the types related to the LinkType value.
     *
     * @param                                    $linkType
     * @param                                    $locale
     * @param FormBuilderInterface|FormInterface $form
     * @param FormBuilderInterface               $builder
     */
    protected function manageLinkTypeRelatedFields($linkType, $locale, $form, FormBuilderInterface $builder)
    {
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
}
