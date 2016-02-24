<?php

namespace Victoire\Bundle\SeoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\MediaBundle\Form\Type\MediaType;

/**
 */
class PageSeoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('metaTitle', null, [
                'label' => 'form.pageSeo.metaTitle.label',
                'attr' => ['placeholder' => 'form.pageSeo.metaTitle.placeholder'],
            ])
            ->add('metaDescription', null, [
                'label' => 'form.pageSeo.metaDescription.label',
                'attr' => ['placeholder' => 'form.pageSeo.metaDescription.placeholder'],
            ])
            ->add('relAuthor', null, [
                'label' => 'form.pageSeo.relAuthor.label',
                'attr' => ['placeholder' => 'form.pageSeo.relAuthor.placeholder'],
            ])
            ->add('relPublisher', null, [
                'label' => 'form.pageSeo.relPublisher.label',
                'attr' => ['placeholder' => 'form.pageSeo.relPublisher.placeholder'],
            ])
            ->add('ogTitle', null, [
                'label' => 'form.pageSeo.ogTitle.label',
                'vic_help_block' => 'form.pageSeo.ogTitle.vic_help_block',
            ])
            ->add('ogType', null, [
                'label' => 'form.pageSeo.ogType.label',
                'attr' => ['placeholder' => 'form.pageSeo.ogType.placeholder'],
            ])
            ->add('ogImage', MediaType::class, [
                'label' => 'form.pageSeo.ogImage.label',
            ])
            ->add('ogUrl', null, [
                'label' => 'form.pageSeo.ogUrl.label',
                'attr' => ['placeholder' => 'form.pageSeo.ogUrl.placeholder'],
            ])
            ->add('ogDescription', null, [
                'label' => 'form.pageSeo.ogDescription.label',
            ])
            ->add('fbAdmins', null, [
                'label' => 'form.pageSeo.fbAdmins.label',
                'attr' => ['placeholder' => 'form.pageSeo.fbAdmins.placeholder'],
            ])
            ->add('twitterCard', ChoiceType::class,
                [
                    'label' => 'form.pageSeo.twitterCard.label',
                    'choices' => [
                        'form.pageSeo.twitterCard.summary.label' => 'summary',
                        'form.pageSeo.twitterCard.summary_large_image.label' => 'summary_large_image',
                        'form.pageSeo.twitterCard.photo.label' => 'photo',
                        'form.pageSeo.twitterCard.app.label' => 'app',
                        'form.pageSeo.twitterCard.player.label' => 'player',
                        'form.pageSeo.twitterCard.product.label' => 'product',
                    ],
                    'choices_as_values' => true,
                    'preferred_choices' => ['summary'],
                    'vic_help_block' => 'form.pageSeo.twitterCard.vic_help_block',
            ])
            ->add('twitterUrl', null, [
                'label' => 'form.pageSeo.twitterUrl.label',
                'attr' => ['placeholder' => 'form.pageSeo.twitterUrl.placeholder'],
            ])
            ->add('twitterTitle', null, [
                'label' => 'form.pageSeo.twitterTitle.label',
                'attr' => ['placeholder' => 'form.pageSeo.twitterTitle.placeholder'],
            ])
            ->add('twitterDescription', null, [
                'label' => 'form.pageSeo.twitterDescription.label',
                'attr' => ['placeholder' => 'form.pageSeo.twitterDescription.placeholder'],
            ])
            ->add('twitterCreator', null, [
                'label' => 'form.pageSeo.twitterCreator.label',
                'attr' => ['placeholder' => 'form.pageSeo.twitterCreator.placeholder'],
            ])
            ->add('twitterImage', MediaType::class, [
                'label' => 'form.pageSeo.twitterImage.label',
            ])
            ->add('schemaPageType', null, [
                'label' => 'form.pageSeo.schemaPageType.label',
                'vic_help_block' => 'form.pageSeo.schemaPageType.vic_help_block',
            ])
            ->add('schemaName', null, [
                'label' => 'form.pageSeo.schemaName.label',
            ])
            ->add('schemaDescription', null, [
                'label' => 'form.pageSeo.schemaDescription.label',
            ])
            ->add('schemaImage', MediaType::class, [
                'label' => 'form.pageSeo.schemaImage.label',
            ])
            ->add('metaRobotsIndex', ChoiceType::class, [
                'choices_as_values' => true,
                'label' => 'form.pageSeo.metaRobotsIndex.label',
                'choices' => [
                    'form.pageSeo.metaRobotsIndex.values.index' => 'index',
                    'form.pageSeo.metaRobotsIndex.values.noindex' => 'noindex',
                ],
            ])
            ->add('metaRobotsFollow', ChoiceType::class, [
                'choices_as_values' => true,
                'label' => 'form.pageSeo.metaRobotsFollow.label',
                'choices' => [
                    'form.pageSeo.metaRobotsFollow.values.follow' => 'follow',
                    'form.pageSeo.metaRobotsFollow.values.nofollow' => 'nofollow',
                ],
            ])
            ->add('metaRobotsAdvanced', null, [
                'label' => 'form.pageSeo.metaRobotsAdvanced.label',
            ])
            ->add('sitemapIndexed', null, [
                'label' => 'form.pageSeo.sitemapIndexed.label',
            ])
            ->add('sitemapChangeFreq', ChoiceType::class,
                [
                    'label' => 'form.pageSeo.sitemapChangeFreq.label',
                    'choices' => [
                        'form.pageSeo.sitemapChangeFreq.format.choices.always.label' => 'always',
                        'form.pageSeo.sitemapChangeFreq.format.choices.hourly.label' => 'hourly',
                        'form.pageSeo.sitemapChangeFreq.format.choices.daily.label' => 'daily',
                        'form.pageSeo.sitemapChangeFreq.format.choices.weekly.label' => 'weekly',
                        'form.pageSeo.sitemapChangeFreq.format.choices.monthly.label' => 'monthly',
                        'form.pageSeo.sitemapChangeFreq.format.choices.yearly.label' => 'yearly',
                        'form.pageSeo.sitemapChangeFreq.format.choices.never.label' => 'never',
                    ],
                    'choices_as_values' => true,
                    'preferred_choices' => ['monthly'],
            ])
            ->add('sitemapPriority', null, [
                'label' => 'form.pageSeo.sitemapPriority.label',
            ])
            ->add('sitemapPriority', ChoiceType::class, [
                'label' => 'form.pageSeo.sitemapPriority.label',
                'choices' => array_combine(range(0, 1, 0.1), range(0, 1, 0.1)),
                'choices_as_values' => true,
            ])
            ->add('relCanonical', null, [
                'label' => 'form.pageSeo.relCanonical.label',
            ])
            ->add('keyword', null, [
                'label' => 'form.pageSeo.keyword.label',
                'attr' => ['placeholder' => 'form.pageSeo.keyword.placeholder'],
            ])
            ->add('redirectTo', null, [
                'label' => 'form.pageSeo.redirectTo.label',
                'vic_help_block' => 'form.pageSeo.redirectTo.vic_help_block',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Victoire\Bundle\SeoBundle\Entity\PageSeo',
            'translation_domain' => 'victoire',
        ]);
    }
}
