<?php

namespace Victoire\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\FormBundle\Form\DataTransformer\StringToSlugTransformer;

class SlugType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new StringToSlugTransformer();
        $builder->addModelTransformer($transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allow_empty'] = $options['allow_empty'];
        parent::buildView($view, $form, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('allow_empty', false);
        parent::configureOptions($resolver);
    }

    public function getParent()
    {
        return TextType::class;
    }
}
