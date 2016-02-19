<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\BlogBundle\Form\DataTransformer\TagToStringTransformer;

class TagsType extends AbstractType
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new TagToStringTransformer($this->entityManager);
        $builder->addViewTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['enable_creation'] = $options['enable_creation'];
        $view->vars['tags'] = [];
        foreach ($view->vars['choices'] as $choice) {
            $view->vars['tags'][$choice->data->getId()] = $choice->data->__toString();
        }

        if ($view->vars['multiple']) {
            $view->vars['tag_values'] = '';
            foreach ($view->vars['value'] as $value) {
                $view->vars['tag_values'] .= $value;

                if ($value !== end($view->vars['value'])) {
                    $view->vars['tag_values'] .= ',';
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'tags';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'enable_creation' => true,
            'class'           => 'Victoire\Bundle\BlogBundle\Entity\Tag',
        ]);
    }
}
