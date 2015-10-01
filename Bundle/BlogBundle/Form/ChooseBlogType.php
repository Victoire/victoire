<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Choose Blog form type.
 */
class ChooseBlogType extends AbstractType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('blog', 'entity', [
                'label'             => 'victoire.blog.choose.blog.label',
                'class'             => 'Victoire\Bundle\BlogBundle\Entity\Blog',
                'property'          => 'name',
                'preferred_choices' => $options['blog'] ? [$options['blog']] : [],
            ]
        );
    }

    /**
     * bind to Page entity.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'victoire',
                'blog'               => null,
            ]
        );
    }

    /**
     * get form name.
     *
     * @return string The name of the form
     */
    public function getName()
    {
        return 'victoire_blog_choose_type';
    }
}
