<?php

namespace Victoire\Bundle\SitemapBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Sitemap Priority PageSeo Type.
 */
class SitemapPriorityPageSeoType extends AbstractType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sitemapPriority', ChoiceType::class, [
                'label'             => 'sitemap.form.priority.label',
                'choices'           => array_combine(range(0, 1, 0.1), range(0, 1, 0.1)),
            ])
            ->add('sitemapIndexed', CheckboxType::class, [
                'label' => false,
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
