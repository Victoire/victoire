<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Category form type.
 */
class CategoryType extends AbstractType
{
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'label'       => 'blog.form.title.label',
                'required'    => true,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ],
                'attr'          => [
                    'class'     => 'vic-blogCategoryWidget-formControl',
                    ],

                ]
            )
            ->remove('removeButton');

        /*
         * When we are editing a menu, we must add the sub menus if there are some children in the entity
         */
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function ($event) {
                $entity = $event->getData();

                if ($entity !== null) {
                    $nbChildren = count($entity->getChildren());
                    // error_log($nbChildren);

                    if ($nbChildren > 0) {
                        $form = $event->getForm();
                        $this->addChildrenField($form);
                    }
                }
            }
        );

        /*
         * we use the PRE_SUBMIT event to avoid having a circular reference
         *
         * This is done when a widget is created in js in the view
         */
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function ($event) {
                $rawData = $event->getData();
                if (isset($rawData['children'])) {
                    $addChildren = true;
                } else {
                    $addChildren = false;
                }

                //did some children was added in the form
                if ($addChildren) {
                    $form = $event->getForm();
                    $this->addChildrenField($form);
                }
            }
        );
    }

    /**
     * Add the items field to the form.
     *
     * @param Form $form
     */
    protected function addChildrenField($form)
    {
        $form->add('children', 'collection',
            [
                'type'          => 'victoire_form_blog_category',
                'required'      => false,
                'allow_add'     => true,
                'allow_delete'  => true,
                'prototype'     => true,
                'by_reference'  => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\BlogBundle\Entity\Category',
            'cascade_validation' => true,
            'translation_domain' => 'victoire',

        ]);
    }
}
