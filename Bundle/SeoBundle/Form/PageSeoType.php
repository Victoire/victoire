<?php

namespace Victoire\Bundle\SeoBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\FormBundle\Form\Type\LinkType;
use Victoire\Bundle\MediaBundle\Form\Type\MediaType;
use Victoire\Bundle\SeoBundle\Entity\PageSeoTranslation;

class PageSeoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', TranslationsType::class, [
                'fields' => [
                    'metaTitle' => [
                        'label' => 'form.pageSeo.metaTitle.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.metaTitle.placeholder',
                        ],
                    ],
                    'metaDescription' => [
                        'label' => 'form.pageSeo.metaDescription.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.metaDescription.placeholder',
                        ],
                    ],
                    'relAuthor' => [
                        'label' => 'form.pageSeo.relAuthor.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.relAuthor.placeholder',
                        ],
                    ],
                    'relPublisher' => [
                        'label' => 'form.pageSeo.relPublisher.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.relPublisher.placeholder',
                        ],
                    ],
                    'ogTitle' => [
                        'label'          => 'form.pageSeo.ogTitle.label',
                        'vic_help_block' => 'form.pageSeo.ogTitle.vic_help_block',
                    ],
                    'ogType' => [
                        'label' => 'form.pageSeo.ogType.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.ogType.placeholder',
                        ],
                    ],
                    'ogImage' => [
                        'field_type' => MediaType::class,
                        'label'      => 'form.pageSeo.ogImage.label',
                    ],
                    'ogUrl' => [
                        'label' => 'form.pageSeo.ogUrl.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.ogUrl.placeholder',
                        ],
                    ],
                    'ogDescription' => [
                        'label' => 'form.pageSeo.ogDescription.label',
                    ],
                    'fbAdmins' => [
                        'label' => 'form.pageSeo.fbAdmins.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.fbAdmins.placeholder',
                        ],
                    ],
                    'twitterCard' => [
                            'field_type' => ChoiceType::class,
                            'label'      => 'form.pageSeo.twitterCard.label',
                            'choices'    => [
                                'form.pageSeo.twitterCard.summary.label'             => 'summary',
                                'form.pageSeo.twitterCard.summary_large_image.label' => 'summary_large_image',
                                'form.pageSeo.twitterCard.photo.label'               => 'photo',
                                'form.pageSeo.twitterCard.app.label'                 => 'app',
                                'form.pageSeo.twitterCard.player.label'              => 'player',
                                'form.pageSeo.twitterCard.product.label'             => 'product',
                            ],
                            'choices_as_values' => true,
                            'preferred_choices' => ['summary'],
                            'vic_help_block'    => 'form.pageSeo.twitterCard.vic_help_block',
                        ],
                    'twitterUrl' => [
                        'label' => 'form.pageSeo.twitterUrl.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.twitterUrl.placeholder',
                        ],
                    ],
                    'twitterTitle' => [
                        'label' => 'form.pageSeo.twitterTitle.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.twitterTitle.placeholder',
                        ],
                    ],
                    'twitterDescription' => [
                        'label' => 'form.pageSeo.twitterDescription.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.twitterDescription.placeholder',
                        ],
                    ],
                    'twitterCreator' => [
                        'label' => 'form.pageSeo.twitterCreator.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.twitterCreator.placeholder',
                        ],
                    ],
                    'twitterImage' => [
                        'field_type' => MediaType::class,
                        'label'      => 'form.pageSeo.twitterImage.label',
                    ],
                    'schemaPageType' => [
                        'label'          => 'form.pageSeo.schemaPageType.label',
                        'vic_help_block' => 'form.pageSeo.schemaPageType.vic_help_block',
                    ],
                    'schemaName' => [
                        'label' => 'form.pageSeo.schemaName.label',
                    ],
                    'schemaDescription' => [
                        'label' => 'form.pageSeo.schemaDescription.label',
                    ],
                    'schemaImage' => [
                        'field_type' => MediaType::class,
                        'label'      => 'form.pageSeo.schemaImage.label',
                    ],
                    'metaRobotsIndex' => [
                        'field_type'        => ChoiceType::class,
                        'choices_as_values' => true,
                        'label'             => 'form.pageSeo.metaRobotsIndex.label',
                        'choices'           => [
                            'form.pageSeo.metaRobotsIndex.values.index'   => 'index',
                            'form.pageSeo.metaRobotsIndex.values.noindex' => 'noindex',
                        ],
                    ],
                    'metaRobotsFollow' => [
                        'field_type'        => ChoiceType::class,
                        'choices_as_values' => true,
                        'label'             => 'form.pageSeo.metaRobotsFollow.label',
                        'choices'           => [
                            'form.pageSeo.metaRobotsFollow.values.follow'   => 'follow',
                            'form.pageSeo.metaRobotsFollow.values.nofollow' => 'nofollow',
                        ],
                    ],
                    'metaRobotsAdvanced' => [
                        'label' => 'form.pageSeo.metaRobotsAdvanced.label',
                    ],
                    'sitemapIndexed' => [
                        'label' => 'form.pageSeo.sitemapIndexed.label',
                        'data'  => $builder->getData() ? $builder->getData()->isSitemapIndexed() : PageSeoTranslation::SITEMAP_INDEXED_DEFAULT,
                    ],
                    'sitemapChangeFreq' => [
                        'field_type' => ChoiceType::class,
                        'label'      => 'form.pageSeo.sitemapChangeFreq.label',
                        'choices'    => [
                            'form.pageSeo.sitemapChangeFreq.format.choices.always.label'  => 'always',
                            'form.pageSeo.sitemapChangeFreq.format.choices.hourly.label'  => 'hourly',
                            'form.pageSeo.sitemapChangeFreq.format.choices.daily.label'   => 'daily',
                            'form.pageSeo.sitemapChangeFreq.format.choices.weekly.label'  => 'weekly',
                            'form.pageSeo.sitemapChangeFreq.format.choices.monthly.label' => 'monthly',
                            'form.pageSeo.sitemapChangeFreq.format.choices.yearly.label'  => 'yearly',
                            'form.pageSeo.sitemapChangeFreq.format.choices.never.label'   => 'never',
                        ],
                        'choices_as_values' => true,
                        'preferred_choices' => ['monthly'],
                    ],
                    'sitemapPriority' => [
                        'field_type'        => ChoiceType::class,
                        'label'             => 'form.pageSeo.sitemapPriority.label',
                        'choices'           => array_combine(range(0, 1, 0.1), range(0, 1, 0.1)),
                        'data'              => $builder->getData() ? $builder->getData()->getSitemapPriority() : PageSeoTranslation::SITEMAP_PRIORITY_DEFAULT,
                    ],
                    'relCanonical' => [
                        'label' => 'form.pageSeo.relCanonical.label',
                    ],
                    'keyword' => [
                        'label' => 'form.pageSeo.keyword.label',
                        'attr'  => [
                            'placeholder' => 'form.pageSeo.keyword.placeholder',
                        ],
                    ],
                    'redirectTo' => [
                        'field_type'     => LinkType::class,
                        'refresh-target' => '#basics',
                        'label'          => 'form.pageSeo.redirectTo.label',
                        'vic_help_block' => 'form.pageSeo.redirectTo.vic_help_block',
                    ],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\SeoBundle\Entity\PageSeo',
            'translation_domain' => 'victoire',
        ]);
    }
}
