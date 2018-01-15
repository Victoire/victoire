<?php

namespace Victoire\Bundle\PageBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Victoire\Bundle\PageBundle\Entity\PageStatus;

/**
 * Edit Page Type.
 */
class PageSettingsType extends PageType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('status', ChoiceType::class, [
            'label'   => 'form.page.type.status.label',
            'choices' => [
                'page.status.label.draft'       => PageStatus::DRAFT,
                'page.status.label.published'   => PageStatus::PUBLISHED,
                'page.status.label.unpublished' => PageStatus::UNPUBLISHED,
                'page.status.label.scheduled'   => PageStatus::SCHEDULED,
            ],
            'choices_as_values' => true,
        ])
        ->add('publishedAt', null, [
            'widget'             => 'single_text',
            'vic_datetimepicker' => true,
        ]);
    }
}
