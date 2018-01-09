<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\PageBundle\Entity\PageStatus;

/**
 * Edit Blog Type.
 */
class BlogSettingsType extends BlogType
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
        $builder
            ->add('slug', null, [
                'label' => 'form.page.type.slug.label',
            ])
            ->add('status', ChoiceType::class, [
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

    /**
     * bind to Page entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'cascade_validation' => 'true',
            ]
        );
    }
}
