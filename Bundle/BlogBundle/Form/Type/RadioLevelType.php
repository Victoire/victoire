<?php

namespace Victoire\Bundle\BlogBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RadioLevelType extends RadioType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'radio';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'radio_level';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(['level' => 0]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars = array_replace($view->vars, [
            'level' => $options['level'],
        ]);
    }
}
