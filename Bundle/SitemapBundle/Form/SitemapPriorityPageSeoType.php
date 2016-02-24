<?php

namespace Victoire\Bundle\SitemapBundle\Form;

use Symfony\Component\Form\AbstractType;
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
        //Generate an array from 0 to 1 with a 0.1 step
        $choices = range(0, 1, 0.1);
        $builder->add('sitemapPriority', ChoiceType::class,
            [
                'label'   => 'sitemap.form.priority.label',
                'choices' => array_combine($choices, $choices),
            ]
        );
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
