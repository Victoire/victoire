<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\PageBundle\Entity\PageStatus;

class ArticleSettingsType extends ArticleType
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
                'label'              => 'form.article.settings.type.publish.label',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('validation_groups', ['edition', 'Default']);
    }
}
