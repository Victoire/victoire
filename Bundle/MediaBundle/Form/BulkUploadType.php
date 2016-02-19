<?php

namespace Victoire\Bundle\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * BulkUploadType.
 */
class BulkUploadType extends AbstractType
{
    /**
     * @var string
     */
    protected $accept;

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
        $builder->add(
                'files', 'file', [
                'required' => false,
                'attr'     => [
                    'accept'   => $this->accept,
                    'multiple' => 'multiple',
                ],
                'data_class' => null,
                ]
        );
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'accept' => '*/*'
        ]);
    }
}
