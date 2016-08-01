<?php

namespace Victoire\Bundle\TemplateBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\Form\ViewType;

/**
 * Template type.
 */
class TemplateType extends ViewType
{
    protected $layouts;

    /**
     * constructor.
     *
     * @param $layouts
     * @param RequestStack $availableLocales
     * @param RequestStack $requestStack
     */
    public function __construct($layouts, $availableLocales, RequestStack $requestStack)
    {
        parent::__construct($availableLocales, $requestStack);
        $this->layouts = $layouts;
    }

    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('layout', ChoiceType::class, [
            'label'             => 'form.template.type.layout.label',
            'choices'           => array_flip($options['layouts']),
            'choices_as_values' => true,
        ]);

        $builder->add('translations', TranslationsType::class, [
            'fields' => [
                'name' => [
                    'label' => 'form.view.type.name.label',
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'Victoire\Bundle\TemplateBundle\Entity\Template',
                'translation_domain' => 'victoire',
                'layouts'            => $this->layouts,
            ]
        );
    }
}
