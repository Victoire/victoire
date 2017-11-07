<?php

namespace Victoire\Bundle\SeoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\SeoBundle\Model\RedirectionList;

/**
 * Class RedirectionListType.
 */
class RedirectionListType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('redirections', CollectionType::class, [
                'type' => RedirectionType::class
//                'type' => RedirectionListType::class
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RedirectionList::class
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'seo_bundle_redirection_list';
    }
}