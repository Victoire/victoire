<?php

namespace Victoire\Bundle\SeoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\FormBundle\Form\Type\LinkType;
use Victoire\Bundle\SeoBundle\Entity\Redirection;

/**
 * Class RedirectionType.
 */
class RedirectionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (true === $options['withUrl']) {
            $builder->add('url', UrlType::class, [
                'label' => false,
                'attr'  => [
                    'class'       => 'vic-form-control',
                    'placeholder' => 'url',
                ],
            ]);
        }

        $builder->add('link', LinkType::class, [
            'label'          => false,
            'withTarget'     => false,
            'refresh-target' => $options['containerId'],
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['containerId']);

        $resolver->setDefaults([
            'method'             => 'POST',
            'translation_domain' => 'victoire',
            'data_class'         => Redirection::class,
            'withUrl'            => false,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'seo_bundle_redirection';
    }
}
