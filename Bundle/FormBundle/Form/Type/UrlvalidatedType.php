<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CriteriaBundle\Entity\Criteria;
use Victoire\Bundle\CriteriaBundle\Form\Type\CriteriaType;
use Victoire\Bundle\WidgetBundle\Model\Widget;

/**
 * Form field with some slug validation and domain prefix
 */
class UrlvalidatedType extends AbstractType
{
    public function getParent()
    {
        return SlugType::class;
    }
}
