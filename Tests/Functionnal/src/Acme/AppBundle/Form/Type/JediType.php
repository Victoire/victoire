<?php

namespace Acme\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JediType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                    'label' => 'acme.app.jedi.form.name.label',
                ])
            ->add('midiChlorians', null, [
                    'label' => 'acme.app.jedi.form.midiChlorians.label',
                ])
            ->add('side', ChoiceType::class, [
                    'label'   => 'acme.app.jedi.form.side.label',
                    'choices' => [
                        'both'   => 'acme.app.jedi.form.side.choice.both',
                        'dark'   => 'acme.app.jedi.form.side.choice.dark',
                        'bright' => 'acme.app.jedi.form.side.choice.bright',
                    ],
                ])
            ->add('slug', null, [
                    'label' => 'acme.app.jedi.form.slug.label',
                ])
            ->add('createdAt', null, [
                    'label' => 'acme.app.jedi.form.createdAt.label',
                ])
            ->add('updatedAt', null, [
                    'label' => 'acme.app.jedi.form.updatedAt.label',
                ])
            ->remove('proxy')
            ->remove('visibleOnFront');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
                'data_class'         => 'Acme\AppBundle\Entity\Jedi',
                'widget'             => 'Force',
                'translation_domain' => 'victoire',
            ]);
    }
}
