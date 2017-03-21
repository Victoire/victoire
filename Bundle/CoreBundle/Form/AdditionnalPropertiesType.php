<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;

/**
 * Create an entity proxy for the widget.*.
 */
class AdditionnalPropertiesType extends AbstractType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $matches = [];
        /** @var APIBusinessEntity $businessEntity */
        $businessEntity = $options['businessEntity'];
        $getMethod = $businessEntity->getGetMethod();
        preg_match_all('/{{([a-zA-Z]+)}}/', $getMethod, $matches);
        $identifiers = array_map(function($property) {
            return $property->getName();
        },
            $businessEntity->getBusinessIdentifiers()->toArray()
        );
        foreach ($matches[1] as $match) {
            if (!in_array($match, $identifiers)) {
                if ($property = $businessEntity->getBusinessPropertyByName($match)) {
                    if (!$property->getChoices() && !$property->getListMethod()) {
                        $builder->add(
                            $match,
                            TextType::class,
                            [
                                'label'    => false,
                                'required' => false,
                            ]
                        );
                    } elseif ($property->getListMethod()) {
                        //TODO
                    } elseif ($choices = $property->getChoices()) {
                        $builder->add(
                            $match,
                            ChoiceType::class,
                            [
                                'choices'  => $choices,
                                'label'    => false,
                                'required' => false,
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * bind to Menu entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'businessEntity' => null,
            ]
        );
    }
}
