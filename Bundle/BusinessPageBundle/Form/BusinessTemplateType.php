<?php

namespace Victoire\Bundle\BusinessPageBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\Form\ViewType;

/**
 * BusinessTemplateType.
 */
class BusinessTemplateType extends ViewType
{
    /*
    * Constructor
    */

    public function __construct($availableLocales, RequestStack $requestStack)
    {
        parent::__construct($availableLocales, $requestStack);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('translations');
        $builder->add('translations', TranslationsType::class, [
            'required_locales' => [],
            'fields'           => [
                'name' => [
                    'label'                        => 'form.view.type.name.label',
                    'vic_business_properties'      => $options['vic_business_properties'],
                    'vic_business_property_picker' => [
                        'description' => 'victoire.form.business_template.name.vic_business_property_picker',
                    ],
                ],
                'slug' => [
                    'label'                        => 'victoire.form.business_template.slug.label',
                    'vic_business_properties'      => $options['vic_business_properties'],
                    'vic_business_property_picker' => [
                        'description' => 'victoire.form.business_template.slug.vic_business_property_picker',
                    ],
                ],
            ],
        ]);
        $builder
            ->add('backendName', null, [
                'label'          => 'victoire.form.business_template.backend_name.label',
                'vic_help_block' => 'victoire.form.business_template.backend_name.help_block',
            ])
            ->add('authorRestricted', null, [
                'label' => 'victoire.form.business_template.author_restricted.label',
            ])
            ->add('query', null, [
                'label'                        => 'victoire.form.business_template.query.label',
                'vic_business_properties'      => $options['vic_business_properties'],
                'vic_business_property_picker' => [
                    'description' => false,
                ],
            ])
            ->add('businessEntityId', HiddenType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefined(['vic_business_properties']);
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate',
            'translation_domain' => 'victoire',
        ]);
    }
}
