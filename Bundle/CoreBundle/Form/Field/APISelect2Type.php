<?php

namespace Victoire\Bundle\CoreBundle\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class APISelect2Type extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['businessEntity'] = $options['businessEntity'];
    }

    /**
     * bind to Menu entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(
        OptionsResolver $resolver
    ) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'businessEntity' => null,
            ]
        );
    }
}
