<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Widget\ListingBundle\Form\WidgetListingItemType;

/**
 * Category form type
 */
class CategoryType extends AbstractType
{
    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'label'    => 'blog.form.title.label',
                'required' => true,
                'constraints' => array(
                    new \Symfony\Component\Validator\Constraints\NotBlank()
                ),
                'attr'          => array(
                    "class"     => "vic-blogCategoryWidget-formControl"
                    )

                )
            )
            ->remove('removeButton');

        /*
         * When we are editing a menu, we must add the sub menus if there are some children in the entity
         */
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function($event) {
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
            function($event) {
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
     * Add the items field to the form
     *
     * @param Form $form
     */
    protected function addChildrenField($form)
    {
        $form->add('children', 'collection',
            array(
                'type' => 'victoire_form_blog_category',
                'required'     => false,
                'allow_add'    => true,
                'allow_delete' => true,
                'prototype'     => true,
                'by_reference' => false
            )
        );
    }
    /**
     * bind form to WidgetRedactor entity
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'data_class'         => 'Victoire\Bundle\BlogBundle\Entity\Category',
            'cascade_validation' => true,
            'translation_domain' => 'victoire'

        ));
    }

    /**
     * get form name
     *
     * @return string The name of the form
     */
    public function getName()
    {
        return 'victoire_form_blog_category';
    }
}
