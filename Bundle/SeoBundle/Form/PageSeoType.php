<?php

namespace Victoire\Bundle\SeoBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\SeoBundle\DataTransformer\PageToIdTransformer;

/**
 *
 * @author Paul Andrieux
 *
 */
class PageSeoType extends AbstractType
{
    /**
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $this->em;
        $pageToIdTransformer = new PageToIdTransformer($entityManager);

        $builder
            ->add('metaTitle', null, array(
                'label' => 'form.pageSeo.metaTitle.label',
                'attr'  => array('placeholder' => 'form.pageSeo.metaTitle.placeholder'),
            ))
            ->add('metaDescription', null, array(
                'label' => 'form.pageSeo.metaDescription.label',
                'attr'  => array('placeholder' => 'form.pageSeo.metaDescription.placeholder'),
            ))
            ->add('relAuthor', null, array(
                'label' => 'form.pageSeo.relAuthor.label',
                'attr'  => array('placeholder' => 'form.pageSeo.relAuthor.placeholder'),
            ))
            ->add('relPublisher', null, array(
                'label' => 'form.pageSeo.relPublisher.label',
                'attr'  => array('placeholder' => 'form.pageSeo.relPublisher.placeholder'),
            ))
            ->add('ogTitle', null, array(
                'label'      => 'form.pageSeo.ogTitle.label',
                'vic_help_block' => 'form.pageSeo.ogTitle.vic_help_block',
            ))
            ->add('ogType', null, array(
                'label' => 'form.pageSeo.ogType.label',
                'attr'  => array('placeholder' => 'form.pageSeo.ogType.placeholder'),
            ))
            ->add('ogImage', 'media', array(
                'label' => 'form.pageSeo.ogImage.label',
            ))
            ->add('ogUrl', null, array(
                'label' => 'form.pageSeo.ogUrl.label',
                'attr'  => array('placeholder' => 'form.pageSeo.ogUrl.placeholder'),
            ))
            ->add('ogDescription', null, array(
                'label' => 'form.pageSeo.ogDescription.label',
            ))
            ->add('fbAdmins', null, array(
                'label' => 'form.pageSeo.fbAdmins.label',
                'attr'  => array('placeholder' => 'form.pageSeo.fbAdmins.placeholder'),
            ))
            ->add('twitterCard', 'choice',
                array(
                    'label'   => 'form.pageSeo.twitterCard.label',
                    'choices' => array(
                        'summary'               => 'form.pageSeo.twitterCard.summary.label',
                        'summary_large_image'   => 'form.pageSeo.twitterCard.summary_large_image.label',
                        'photo'                 => 'form.pageSeo.twitterCard.photo.label',
                        'app'                   => 'form.pageSeo.twitterCard.app.label',
                        'player'                => 'form.pageSeo.twitterCard.player.label',
                        'product'               => 'form.pageSeo.twitterCard.product.label',
                    ),
                    'preferred_choices' => array('summary'),
                    'vic_help_block' => 'form.pageSeo.twitterCard.vic_help_block',
            ))
            ->add('twitterUrl', null, array(
                'label' => 'form.pageSeo.twitterUrl.label',
                'attr'  => array('placeholder' => 'form.pageSeo.twitterUrl.placeholder'),
            ))
            ->add('twitterTitle', null, array(
                'label' => 'form.pageSeo.twitterTitle.label',
                'attr'  => array('placeholder' => 'form.pageSeo.twitterTitle.placeholder'),
            ))
            ->add('twitterDescription', null, array(
                'label' => 'form.pageSeo.twitterDescription.label',
                'attr'  => array('placeholder' => 'form.pageSeo.twitterDescription.placeholder'),
            ))
            ->add('twitterImage', 'media', array(
                'label' => 'form.pageSeo.twitterImage.label',
            ))
            ->add('schemaPageType', null, array(
                'label' => 'form.pageSeo.schemaPageType.label',
                'vic_help_block' => 'form.pageSeo.schemaPageType.vic_help_block',
            ))
            ->add('schemaName', null, array(
                'label' => 'form.pageSeo.schemaName.label',
            ))
            ->add('schemaDescription', null, array(
                'label' => 'form.pageSeo.schemaDescription.label',
            ))
            ->add('schemaImage', 'media', array(
                'label' => 'form.pageSeo.schemaImage.label',
            ))
            ->add('metaRobotsIndex', 'choice', array(
                'label' => 'form.pageSeo.metaRobotsIndex.label',
                'choices' => [
                    'index' => 'form.pageSeo.metaRobotsIndex.values.index',
                    'noindex' => 'form.pageSeo.metaRobotsIndex.values.noindex',
                ],
            ))
            ->add('metaRobotsFollow', 'choice', array(
                'label' => 'form.pageSeo.metaRobotsFollow.label',
                'choices' => [
                    'follow' => 'form.pageSeo.metaRobotsFollow.values.follow',
                    'nofollow' => 'form.pageSeo.metaRobotsFollow.values.nofollow',
                ],
            ))
            ->add('metaRobotsAdvanced', null, array(
                'label' => 'form.pageSeo.metaRobotsAdvanced.label',
            ))
            ->add('sitemapIndexed', null, array(
                'label' => 'form.pageSeo.sitemapIndexed.label',
            ))
            ->add('sitemapChangeFreq', 'choice',
                array(
                    'label'   => 'form.pageSeo.sitemapChangeFreq.label',
                    'choices' => array(
                        'always'  => 'form.pageSeo.sitemapChangeFreq.format.choices.always.label',
                        'hourly'  => 'form.pageSeo.sitemapChangeFreq.format.choices.hourly.label',
                        'daily'   => 'form.pageSeo.sitemapChangeFreq.format.choices.daily.label',
                        'weekly'  => 'form.pageSeo.sitemapChangeFreq.format.choices.weekly.label',
                        'monthly' => 'form.pageSeo.sitemapChangeFreq.format.choices.monthly.label',
                        'yearly'  => 'form.pageSeo.sitemapChangeFreq.format.choices.yearly.label',
                        'never'   => 'form.pageSeo.sitemapChangeFreq.format.choices.never.label',
                    ),
                    'preferred_choices' => array('monthly'),
            ))
            ->add('sitemapPriority', null, array(
                'label' => 'form.pageSeo.sitemapPriority.label',
            ))
            ->add('sitemapPriority', 'choice',
                array(
                    'label'   => 'form.pageSeo.sitemapPriority.label',
                    'choices' => array_combine(range(0, 1, 0.1), range(0, 1, 0.1)),
            ))
            ->add('relCanonical', null, array(
                'label' => 'form.pageSeo.relCanonical.label',
            ))
            ->add('keyword', null, array(
                'label' => 'form.pageSeo.keyword.label',
            ))
            ->add('redirectTo', null, array(
                'label'      => 'form.pageSeo.redirectTo.label',
                'vic_help_block' => 'form.pageSeo.redirectTo.vic_help_block',
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Victoire\Bundle\SeoBundle\Entity\PageSeo',
            'translation_domain' => 'victoire',
        ));
    }

    /**
     * The name of the form
     *
     * @return string
     */
    public function getName()
    {
        return 'seo_page';
    }
}
