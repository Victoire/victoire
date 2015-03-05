<?php

namespace Victoire\Bundle\BlogBundle\Form\Extension;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class HierarchyTreeExtension extends AbstractType
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }
    /**
     * Retourne le nom du type de champ qui est étendu
     *
     * @return string Le nom du type qui est étendu
     */
    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'hierarchy_tree';
    }
    /**
     * genere le formulaire
     *
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->vars['choices'] as $choice) {
            $dataNode = $choice->data;
            $level = $this->propertyAccessor->getValue($dataNode, 'lvl');

            $choice->label = str_repeat(str_repeat('&#160;', $level), 4).$choice->label;

        }
    }
}