<?php

namespace Victoire\Bundle\PageBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\FormBundle\Form\Type\UrlvalidatedType;
use Victoire\Bundle\PageBundle\Entity\PageStatus;

/**
 * Edit Page Type.
 */
class PageSettingsType extends PageType
{
    /**
     * Constructor.
     */
    public function __construct($availableLocales, RequestStack $requestStack)
    {
        parent::__construct($availableLocales, $requestStack);
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

        $builder->remove('name');
        $builder->add('translations', 'a2lix_translations', array(
            'fields' => array(
                'name' => array(
                    'label' => 'form.view.type.name.label',
                ),
                'slug' => array(
                    'label' => 'form.page.type.slug.label',
                    'field_type' => UrlvalidatedType::Class,
                )
            )
        ))
        ->add('status', ChoiceType::class, [
            'label'   => 'form.page.type.status.label',
            'choices' => [
                'form.page.type.status.choice.label.draft'       => PageStatus::DRAFT,
                'form.page.type.status.choice.label.published'   => PageStatus::PUBLISHED,
                'form.page.type.status.choice.label.unpublished' => PageStatus::UNPUBLISHED,
                'form.page.type.status.choice.label.scheduled'   => PageStatus::SCHEDULED,
            ],
            'choices_as_values' => true,
        ])
        ->add('publishedAt', null, [
            'widget'             => 'single_text',
            'vic_datetimepicker' => true,
        ]);
    }
}
