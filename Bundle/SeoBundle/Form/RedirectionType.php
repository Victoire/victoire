<?php

namespace Victoire\Bundle\SeoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RedirectionType.
 */
class RedirectionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('input', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'vic-form-control'
                ]
            ])
            ->add('output', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'vic-form-control'
                ]
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\SeoBundle\Entity\Redirection',
            'translation_domain' => 'victoire',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'seo_bundle_redirection';
    }
}