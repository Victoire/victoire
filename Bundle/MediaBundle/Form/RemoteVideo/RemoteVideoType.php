<?php

namespace Victoire\Bundle\MediaBundle\Form\RemoteVideo;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * RemoteVideoType.
 */
class RemoteVideoType extends AbstractType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('code', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => ['youtube' => 'youtube', 'vimeo' => 'vimeo', 'dailymotion' => 'dailymotion']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => 'Victoire\Bundle\MediaBundle\Helper\RemoteVideo\RemoteVideoHelper',
        ]);
    }
}
