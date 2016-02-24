<?php

namespace Victoire\Bundle\BusinessEntityBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;

class BusinessEntityTypeExtension extends AbstractTypeExtension
{
    private $businessEntityHelper;

    public function __construct(BusinessEntityHelper $businessEntityHelper)
    {
        $this->businessEntityHelper = $businessEntityHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!empty($options['data_class'])
            && $this->businessEntityHelper->findByEntityClassname($options['data_class'])) {
            $builder
            ->add('visibleOnFront', ChoiceType::class, [
                'choices' => [
                    'businessEntity.form.visibleOnFront.yes' => 1,
                    'businessEntity.form.visibleOnFront.no' => 0,
                ],
                'choices_as_values' => true,
                'label' => 'businessEntity.form.visibleOnFront.label',
                'translation_domain' => 'victoire',
            ]);
        }
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}
