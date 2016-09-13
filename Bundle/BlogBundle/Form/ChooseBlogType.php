<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\Query\Expr;

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
        $builder->add('blog', EntityType::class, [
                'label'             => 'victoire.blog.choose.blog.label',
                'class'             => 'Victoire\Bundle\BlogBundle\Entity\Blog',
                'property'          => 'name',
                'preferred_choices' => $options['blog'] ? [$options['blog']] : [],
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->joinTranslations($options['locale'])->getInstance();
                },
            ]
        );
    }

    /**
     * bind to Page entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'victoire',
                'blog'               => null,
                'locale'               => null,
            ]
        );
    }
}
