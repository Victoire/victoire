<?php
namespace Victoire\Bundle\SitemapBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Sitemap Priority PageSeo Type
 */
class SitemapPriorityPageSeoType extends AbstractType
{
    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //Generate an array from 0 to 1 with a 0.1 step
        $choices = range(0, 1, 0.1);
        $builder->add('sitemapPriority', 'choice',
            array(
                'label'   => 'sitemap.form.priority.label',
                'choices' => array_combine($choices, $choices),
            )
        );
    }

    /**
     * bind to PageSeo entity
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
     * get form name
     * @return string name
     */
    public function getName()
    {
        return 'victoire_sitemap_priority_pageseo_type';
    }
}
