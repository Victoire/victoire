<?php

namespace Victoire\Bundle\BlogBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CheckboxLevelType extends CheckboxType
{
    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return 'checkbox';
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'checkbox_level';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array("level"=> 0));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars = array_replace($view->vars, array(
            'level' => $options['level']
        ));
    }

}
