<?php

namespace Victoire\Bundle\ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\ConfigBundle\Entity\GlobalConfig;
use Victoire\Bundle\MediaBundle\Form\Type\MediaType;

class GlobalConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('metaTitlePattern', TextType::class, [
                'required' => false,
                'label'    => 'globalConfig.form.metaTitlePattern',
                'attr'     => [
                    'placeholder' => '%page.title%',
                ],
            ])
            ->add('logo', MediaType::class, [
                'required' => false,
                'label'    => 'globalConfig.form.logo',
            ])
            ->add('mainColor', TextType::class, [
                'required' => false,
                'label'    => 'globalConfig.form.mainColor',
            ])
            ->add('head', TextareaType::class, [
                'required' => false,
                'label'    => 'globalConfig.form.head',
                'attr'     => [
                    'rows' => 8,
                ],
            ])
            ->add('organizationJsonLD', TextareaType::class, [
                'required' => false,
                'label'    => 'globalConfig.form.organizationJsonLD',
                'attr'     => [
                    'rows' => 8,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => GlobalConfig::class,
            'translation_domain' => 'victoire',
        ]);
    }
}
