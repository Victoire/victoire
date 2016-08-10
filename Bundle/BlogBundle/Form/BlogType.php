<?php

namespace Victoire\Bundle\BlogBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\PageBundle\Form\BasePageType;

/**
 * Blog form type.
 */
class BlogType extends BasePageType
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

        $builder->add('translations', TranslationsType::class, [
            'required_locales' => [],
            'fields'           => [
                'name' => [
                    'label' => 'form.view.type.name.label',
                ],
            ],
        ]);
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
                'data_class'         => 'Victoire\Bundle\BlogBundle\Entity\Blog',
                'translation_domain' => 'victoire',
            ]
        );
    }
}
