<?php

namespace Acme\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('side', 'choice', [
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
     * bind form to WidgetForce entity.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults([
                'data_class'         => 'Acme\AppBundle\Entity\Jedi',
                'widget'             => 'Force',
                'translation_domain' => 'victoire',
            ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'acme_appbundle_jedi';
    }
}
